document.addEventListener("DOMContentLoaded", () => {

  const form = document.getElementById("formPeriodo");
  const ctx = document.getElementById("graficoTemperatura").getContext("2d");

  const valorMediaTe = document.getElementById("valorMedia");
  const valorMediaDiferenca = document.getElementById("valorMediaDif");

  const URL = "temp_externa.php";

  let chart;

  async function carregarGrafico(inicio, fim) {
    try {
      const resposta = await fetch(`${URL}?formato=json&inicio=${inicio}&fim=${fim}`);
      const dados = await resposta.json();

      // === DADOS DO GRÁFICO ===
      const labels = dados.dados.map(item => item.datahora_completa);
      const valores = dados.dados.map(item => parseFloat(item.te));

      if (chart) chart.destroy();

      const step = 20;
      const labelsReduzidos = labels.filter((_, i) => i % step === 0);
      const valoresReduzidos = valores.filter((_, i) => i % step === 0);

      chart = new Chart(ctx, {
        type: "line",
        data: {
          labels: labelsReduzidos,
          datasets: [{
            label: "Temperatura Externa (°C)",
            data: valoresReduzidos,
            borderColor: "blue",
            borderWidth: 2.5,
            tension: 0.3,
            pointRadius: 2
          }]
        },
        options: {
          responsive: true,
          scales: {
            x: { title: { display: true, text: "Data e Hora" } },
            y: { title: { display: true, text: "Temperatura (°C)" } }
          }
        }
      });

      // === ATUALIZA OS VALORES DAS MÉDIAS ===
      valorMediaTe.textContent = dados.media ?? "--";
      valorMediaDiferenca.textContent = dados.diferenca ?? "--";

    } catch (e) {
      console.error("Erro:", e);
      alert("Erro ao carregar gráfico");
    }
  }

  form.addEventListener("submit", e => {
    e.preventDefault();
    const inicio = document.getElementById("inicio").value;
    const fim = document.getElementById("fim").value;
    carregarGrafico(inicio, fim);
  });

  document.getElementById("inicio").value = "2025-06-01";
  document.getElementById("fim").value = "2025-06-07";

  carregarGrafico("2025-06-01", "2025-06-07");

});
