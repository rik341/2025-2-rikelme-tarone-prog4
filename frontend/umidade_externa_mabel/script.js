document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("formPeriodo");
    const spanMedia = document.getElementById("MediaUmidade");
    const canvasRegistros = document.getElementById("graficoUmidadeexterna");
  
    let chartRegistros = null;
  
    // Período padrão
    const padraoInicio = "2025-06-01";
    const padraoFim = "2025-06-07";
  
    // inicializa inputs de data
    if (document.getElementById("inicio")) document.getElementById("inicio").value = padraoInicio;
    if (document.getElementById("fim")) document.getElementById("fim").value = padraoFim;
  
    async function carregar(inicio = padraoInicio, fim = padraoFim) {
      try {
        const url = `umi_externa.php?formato=json&inicio=${inicio}&fim=${fim}`;
        const resp = await fetch(url);
        if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
  
        const json = await resp.json();
  
        // --- dados vindos do PHP ---
        const registros = json.dados ?? [];
        const media = json.media ?? null;
  
        // atualizar média
        spanMedia.textContent = (media !== null && media !== undefined)
          ? Number(media).toFixed(2)
          : "--";
  
        // --- gráfico da umidade externa ---
        if (!Array.isArray(registros) || registros.length === 0) {
          if (chartRegistros) { chartRegistros.destroy(); chartRegistros = null; }
          return;
        }
  
        const labelsBruto = registros.map(r => r.datahora_completa);
        const valoresBruto = registros.map(r => parseFloat(r.he));
  
        // amostragem para não sobrecarregar o gráfico
        const step = 20;
        const labels = labelsBruto.filter((_, i) => i % step === 0);
        const valores = valoresBruto.filter((_, i) => i % step === 0);
  
        if (chartRegistros) chartRegistros.destroy();
        const ctx = canvasRegistros.getContext("2d");
  
        chartRegistros = new Chart(ctx, {
          type: "line",
          data: {
            labels,
            datasets: [{
              label: "Umidade Externa (%)",
              data: valores,
              borderColor: "#1b76d1",
              borderWidth: 2.5,
              tension: 0.35,
              pointRadius: 0
            }]
          },
          options: {
            responsive: true,
            scales: {
              x: {
                title: { display: true, text: "Data e Hora" }
              },
              y: {
                title: { display: true, text: "Umidade (%)" },
                suggestedMin: 0,
                suggestedMax: 100
              }
            },
            plugins: {
              decimation: {
                enabled: true,
                algorithm: "min-max"
              }
            }
          }
        });
  
      } catch (err) {
        console.error("Erro ao carregar dados:", err);
        spanMedia.textContent = "--";
  
        if (chartRegistros) { chartRegistros.destroy(); chartRegistros = null; }
      }
    }
  
    // carregar inicial
    carregar(padraoInicio, padraoFim);
  
    // evento do formulário
    if (form) {
      form.addEventListener("submit", e => {
        e.preventDefault();
        const inicio = document.getElementById("inicio").value || padraoInicio;
        const fim = document.getElementById("fim").value || padraoFim;
        carregar(inicio, fim);
      });
    }
  });
  