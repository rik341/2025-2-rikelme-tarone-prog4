window.addEventListener("DOMContentLoaded", () => {
  const loading = document.getElementById("loading");
  const canvas = document.getElementById("graficoAqi");

  fetch(`ptqa_aqi.php?inicio=${dataInicial}&fim=${dataFinal}&formato=json`)
    .then(res => res.json())
    .then(dados => {
      loading.style.display = "none";

      if (dados.ruim.length === 0) {
        loading.style.display = "block";
        loading.textContent = "Nenhum registro AQI ≥ 4 encontrado no período.";
        return;
      }

      const labels = dados.ruim.map(d => d.datahora_completa);
      const valores = dados.ruim.map(d => parseFloat(d.aqi));

      const ctx = canvas.getContext("2d");

      new Chart(ctx, {
        type: "line",
        data: {
          labels: labels,
          datasets: [{
            label: "Baixa Qualidade do Ar (AQI ≥ 4)",
            data: valores,
            borderColor: "rgba(200, 50, 50, 1)",
            backgroundColor: "rgba(200, 50, 50, 0.2)",
            fill: true,
            tension: 0.3,
            pointRadius: 3
          }]
        },
        options: {
          responsive: true,
          scales: {
            x: { title: { display: true, text: "Data e Hora" } },
            y: { title: { display: true, text: "AQI" }, beginAtZero: true }
          }
        }
      });
    })
    .catch(() => {
      loading.textContent = "Erro ao carregar dados.";
    });
});
window.addEventListener("DOMContentLoaded", () => {
  const loading = document.getElementById("loading");
  const canvas = document.getElementById("graficoAqi");

  fetch(`ptqa_aqi.php?inicio=${dataInicial}&fim=${dataFinal}&formato=json`)
    .then(res => res.json())
    .then(dados => {
      loading.style.display = "none";

      if (dados.ruim.length === 0) {
        loading.style.display = "block";
        loading.textContent = "Nenhum registro AQI ≥ 4 encontrado no período.";
        return;
      }

      const labels = dados.ruim.map(d => d.datahora_completa);
      const valores = dados.ruim.map(d => parseFloat(d.aqi));

      const ctx = canvas.getContext("2d");

      new Chart(ctx, {
        type: "line",
        data: {
          labels: labels,
          datasets: [{
            label: "Baixa Qualidade do Ar (AQI ≥ 4)",
            data: valores,
            borderColor: "rgba(200, 50, 50, 1)",
            backgroundColor: "rgba(200, 50, 50, 0.2)",
            fill: true,
            tension: 0.3,
            pointRadius: 3
          }]
        },
        options: {
          responsive: true,
          scales: {
            x: { title: { display: true, text: "Data e Hora" } },
            y: { title: { display: true, text: "AQI" }, beginAtZero: true }
          }
        }
      });
    })
    .catch(() => {
      loading.textContent = "Erro ao carregar dados.";
    });
});
