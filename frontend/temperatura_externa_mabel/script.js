document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("formPeriodo");
  const spanMedia = document.getElementById("valorMedia");
  const spanDif = document.getElementById("valorMediaDif");
  const canvasRegistros = document.getElementById("graficoTemperatura");

  let chartRegistros = null;

  // Período padrão
  const padraoInicio = "2025-06-01";
  const padraoFim = "2025-06-07";

  // Inicializa datas
  const inputInicio = document.getElementById("inicio");
  const inputFim = document.getElementById("fim");

  if (inputInicio) inputInicio.value = padraoInicio;
  if (inputFim) inputFim.value = padraoFim;

  async function carregar(inicio = padraoInicio, fim = padraoFim) {
    try {
      const url = `temp_externa.php?formato=json&inicio=${inicio}&fim=${fim}`;
      const resp = await fetch(url);

      if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
      const json = await resp.json();

      const registros = json.dados ?? [];
      const media = json.media ?? null;
      const diferenca = json.diferenca ?? null;

      // Atualiza spans
      spanMedia.textContent = media !== null ? Number(media).toFixed(2) : "--";
      spanDif.textContent = diferenca !== null ? Number(diferenca).toFixed(2) : "--";

      // Caso venha sem dados
      if (!Array.isArray(registros) || registros.length === 0) {
        if (chartRegistros) {
          chartRegistros.destroy();
          chartRegistros = null;
        }
        return;
      }

      // Dados
      const labelsBruto = registros.map(r => r.datahora);
      const valoresBruto = registros.map(r => parseFloat(r.te));

      // Amostragem
      const step = 20;
      const labels = labelsBruto.filter((_, i) => i % step === 0);
      const valores = valoresBruto.filter((_, i) => i % step === 0);

      // Destruir gráfico anterior
      if (chartRegistros) chartRegistros.destroy();

      const ctx = canvasRegistros.getContext("2d");

      chartRegistros = new Chart(ctx, {
        type: "line",
        data: {
          labels,
          datasets: [{
            label: "Temperatura Externa (°C)",
            data: valores,
            borderColor: "#0077ff",
            borderWidth: 2.5,
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
      spanMedia.textContent = "--";
      spanDif.textContent = "--";
      if (chartRegistros) chartRegistros.destroy();
    }
  }

  // Carregar inicial
  carregar(padraoInicio, padraoFim);

  // Evento do formulário
  if (form) {
    form.addEventListener("submit", e => {
      e.preventDefault();
      const inicio = inputInicio.value || padraoInicio;
      const fim = inputFim.value || padraoFim;
      carregar(inicio, fim);
    });
  }
});
