document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("formPeriodo");
  const spanMedia = document.getElementById("valorMedia");
  const spanDif = document.getElementById("valorMediaDif");
  const canvasRegistros = document.getElementById("graficoTemperatura");

  let chartRegistros = null;

  // Período padrão
  const padraoInicio = "2025-06-01";
  const padraoFim = "2025-06-07";

  // inicializa inputs de data se existirem
  if (document.getElementById("inicio")) document.getElementById("inicio").value = padraoInicio;
  if (document.getElementById("fim")) document.getElementById("fim").value = padraoFim;

  async function carregar(inicio = padraoInicio, fim = padraoFim) {
    try {
      const url = `temp_externa.php?formato=json&inicio=${inicio}&fim=${fim}`;
      const resp = await fetch(url);
      if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
      const json = await resp.json();

      // --- ler campos corretos vindos do PHP ---
      const registros = json.dados ?? [];
      const mediaTe = json.media ?? null;
      const diferenca = json.diferenca ?? null;

      // Atualizar spans (média e diferença)
      spanMedia.textContent = (mediaTe !== null && mediaTe !== undefined) ? Number(mediaTe).toFixed(2) : "--";
      spanDif.textContent = (diferenca !== null && diferenca !== undefined) ? Number(diferenca).toFixed(2) : "--";

      // Recuperar o intervalo selecionado pelo usuário (valor do campo de input)
      const intervalo = parseInt(document.getElementById("intervalo").value) || 20; // Pega o valor ou usa 20 como padrão

      // Preparar arrays para gráfico de registros
      if (!Array.isArray(registros) || registros.length === 0) {
        // Limpar gráfico se não houver dados
        if (chartRegistros) { chartRegistros.destroy(); chartRegistros = null; }
      } else {
        const labelsBruto = registros.map(r => r.datahora_completa);
        const valoresBruto = registros.map(r => parseFloat(r.te));

        // Reduzir pontos de acordo com o intervalo selecionado
        const labels = labelsBruto.filter((_, i) => i % intervalo === 0); // Usando o intervalo definido pelo usuário
        const valores = valoresBruto.filter((_, i) => i % intervalo === 0); // Usando o intervalo definido pelo usuário

        if (chartRegistros) chartRegistros.destroy();
        const ctx1 = canvasRegistros.getContext("2d");
        chartRegistros = new Chart(ctx1, {
          type: "line",
          data: {
            labels,
            datasets: [{
              label: "Temperatura Externa (°C)",
              data: valores,
              borderColor: "blue",
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

    } catch (err) {
      console.error("Erro ao carregar dados:", err);
      spanMedia.textContent = "--";
      spanDif.textContent = "--";
      if (chartRegistros) { chartRegistros.destroy(); chartRegistros = null; }
    }
  }

  // Carregar inicial
  carregar(padraoInicio, padraoFim);

  // Evento do formulário
  if (form) {
    form.addEventListener("submit", e => {
      e.preventDefault();
      const inicio = document.getElementById("inicio").value || padraoInicio;
      const fim = document.getElementById("fim").value || padraoFim;
      carregar(inicio, fim);
    });
  }

});
