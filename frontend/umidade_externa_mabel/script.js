document.addEventListener("DOMContentLoaded", () => {

  const form = document.getElementById("formPeriodo");
  const spanMedia = document.getElementById("MediaUmidade");
  const canvas = document.getElementById("graficoUmidadeExterna");

  let grafico = null;

  const padraoInicio = "2025-06-01";
  const padraoFim = "2025-06-07";

  async function carregar(inicio = padraoInicio, fim = padraoFim, intervalo = 20) {

    const url = `umi_externa.php?formato=json&inicio=${inicio}&fim=${fim}`;

    const resp = await fetch(url);
    const json = await resp.json();

    const registros = json.dados || [];
    const media = json.media || null;

    spanMedia.textContent = media ? Number(media).toFixed(2) : "--";

    if (registros.length === 0) {
      if (grafico) grafico.destroy();
      return;
    }

    const labelsBruto = registros.map(r => r.datahora_completa);
    const valoresBruto = registros.map(r => parseFloat(r.he));

    const labels = labelsBruto.filter((_, i) => i % intervalo === 0);
    const valores = valoresBruto.filter((_, i) => i % intervalo === 0);

    if (grafico) grafico.destroy();

    const ctx = canvas.getContext("2d");

    grafico = new Chart(ctx, {
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
          x: { title: { display: true, text: "Data e Hora" } },
          y: {
            title: { display: true, text: "Umidade (%)" },
            suggestedMin: 0,
            suggestedMax: 100
          }
        }
      }
    });
  }

  form.addEventListener("submit", e => {
    e.preventDefault();

    const inicio = document.getElementById("inicio").value || padraoInicio;
    const fim = document.getElementById("fim").value || padraoFim;
    const intervalo = parseInt(document.getElementById("intervalo").value) || 20;

    carregar(inicio, fim, intervalo);
  });

  carregar();
});
