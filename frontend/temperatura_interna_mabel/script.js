document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("formPeriodo");
  const spanMedia = document.getElementById("valorMedia");
  const spanDif = document.getElementById("valorMediaDif");
  const canvasRegistros = document.getElementById("graficoInterna");
  const canvasMediaDiaria = document.getElementById("graficoMediaDiaria");

  let chartRegistros = null;
  let chartMedia = null;

  // Período padrão
  const padraoInicio = "2025-06-01";
  const padraoFim = "2025-06-07";

  // inicializa inputs de data se existirem
  if (document.getElementById("inicio")) document.getElementById("inicio").value = padraoInicio;
  if (document.getElementById("fim")) document.getElementById("fim").value = padraoFim;

  async function carregar(inicio = padraoInicio, fim = padraoFim) {
    try {
      const url = `temp_interna.php?formato=json&inicio=${inicio}&fim=${fim}`;
      const resp = await fetch(url);
      if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
      const json = await resp.json();

      // --- ler campos corretos vindos do PHP ---
      const registros = json.registros_ti ?? [];
      const mediaTi = json.media_ti ?? null;
      const mediaDiaria = json.media_diaria ?? [];
      const diferenca = json.diferenca ?? null;

      // atualizar spans (média e diferença)
      spanMedia.textContent = (mediaTi !== null && mediaTi !== undefined) ? Number(mediaTi).toFixed(2) : "--";
      spanDif.textContent = (diferenca !== null && diferenca !== undefined) ? Number(diferenca).toFixed(2) : "--";

      // --- preparar arrays para gráfico de registros ---
      if (!Array.isArray(registros) || registros.length === 0) {
        // limpa gráficos se não houver dados
        if (chartRegistros) { chartRegistros.destroy(); chartRegistros = null; }
      } else {
        const labelsBruto = registros.map(r => r.datahora_completa);
        const valoresBruto = registros.map(r => parseFloat(r.ti));

        // reduzir pontos (amostragem)
        const step = 20;
        const labels = labelsBruto.filter((_, i) => i % step === 0);
        const valores = valoresBruto.filter((_, i) => i % step === 0);

        if (chartRegistros) chartRegistros.destroy();
        const ctx1 = canvasRegistros.getContext("2d");
        chartRegistros = new Chart(ctx1, {
          type: "line",
          data: {
            labels,
            datasets: [{
              label: "Temperatura Interna (°C)",
              data: valores,
              borderColor: "red",
              borderWidth: 2.5,
              tension: 0.3,
              pointRadius: 0
            }]
          },
          options: {
            responsive: true,
            scales: {
              x: { title: { display: true, text: "Data e Hora" } },
              y: { title: { display: true, text: "Temperatura (°C)" } }
            },
            plugins: {
              decimation: { enabled: true, algorithm: "min-max" }
            }
          }
        });
      }

      // --- gráfico média diária (barra) ---
      if (!Array.isArray(mediaDiaria) || mediaDiaria.length === 0) {
        if (chartMedia) { chartMedia.destroy(); chartMedia = null; }
      } else {
        const labelsMed = mediaDiaria.map(d => d.datainclusao);
        // a coluna do PHP é media_diaria_ti
        const valoresMed = mediaDiaria.map(d => parseFloat(d.media_diaria_ti));

        if (chartMedia) chartMedia.destroy();
        const ctx2 = canvasMediaDiaria.getContext("2d");
        chartMedia = new Chart(ctx2, {
          type: "bar",
          data: {
            labels: labelsMed,
            datasets: [{
              label: "Média Diária da Temp. Interna (°C)",
              data: valoresMed,
              backgroundColor: "orange"
            }]
          },
          options: {
            responsive: true,
            scales: {
              x: { title: { display: true, text: "Data" } },
              y: { title: { display: true, text: "Temperatura (°C)" } }
            }
          }
        });
      }

    } catch (err) {
      console.error("Erro ao carregar dados:", err);
      spanMedia.textContent = "--";
      spanDif.textContent = "--";
      if (chartRegistros) { chartRegistros.destroy(); chartRegistros = null; }
      if (chartMedia) { chartMedia.destroy(); chartMedia = null; }
    }
  }

  // carregar inicial
  carregar(padraoInicio, padraoFim);

  // evento do formulário (se existir)
  if (form) {
    form.addEventListener("submit", e => {
      e.preventDefault();
      const inicio = document.getElementById("inicio").value || padraoInicio;
      const fim = document.getElementById("fim").value || padraoFim;
      carregar(inicio, fim);
    });
  }

});
