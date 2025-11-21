document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("formPeriodo");
    const spanMax = document.getElementById("valormax");
    const spanMin = document.getElementById("valormin");
    const canvas = document.getElementById("graficoTemperatura");
  
    let chart = null;
  
    // Período padrão
    const padraoInicio = "2025-06-01";
    const padraoFim = "2025-06-07";
  
    // Preenche inputs
    if (document.getElementById("inicio")) document.getElementById("inicio").value = padraoInicio;
    if (document.getElementById("fim")) document.getElementById("fim").value = padraoFim;
  
    async function carregar(inicio = padraoInicio, fim = padraoFim) {
      try {
        const url = `ninho.php?formato=json&inicio=${inicio}&fim=${fim}`;
        const resp = await fetch(url);
        if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
  
        const json = await resp.json();
  
        const registros = json.registros ?? [];
        const maxima = json.maximo ?? null;
        const minima = json.minimo ?? null;
  
        // Atualiza valores
        spanMax.textContent = maxima !== null ? Number(maxima).toFixed(2) : "--";
        spanMin.textContent = minima !== null ? Number(minima).toFixed(2) : "--";
  
        // Verifica registros
        if (!Array.isArray(registros) || registros.length === 0) {
          if (chart) { chart.destroy(); chart = null; }
          return;
        }
  
        // Arrays de dados
        const labelsBruto = registros.map(r => r.datahora_completa);
        const valoresBruto = registros.map(r => parseFloat(r.ninho));
  
        // Reduz pontos
        const step = 20;
        const labels = labelsBruto.filter((_, i) => i % step === 0);
        const valores = valoresBruto.filter((_, i) => i % step === 0);
  
        // Destrói gráfico antigo
        if (chart) chart.destroy();
  
        const ctx = canvas.getContext("2d");
        chart = new Chart(ctx, {
          type: "line",
          data: {
            labels,
            datasets: [{
              label: "Temperatura do Ninho (°C)",
              data: valores,
              borderColor: "orange",
              borderWidth: 2.3,
              tension: 0.25,
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
  
      } catch (err) {
        console.error("Erro ao carregar dados:", err);
        spanMax.textContent = "--";
        spanMin.textContent = "--";
  
        if (chart) { chart.destroy(); chart = null; }
      }
    }
  
    // Carregamento inicial
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
  