// Espera o carregamento da página
window.addEventListener("DOMContentLoaded", () => {
    const loading = document.getElementById("loading");
    const canvas = document.getElementById("graficoAqi");
  
    // Busca JSON com os dados do PHP
    fetch(`ptqa_aqi.php?inicio=${dataInicial}&fim=${dataFinal}&formato=json`)
      .then(res => res.json())
      .then(dados => {
        loading.style.display = "none";
  
        if (dados.length === 0) {
          loading.style.display = "block";
          loading.textContent = "Nenhum registro encontrado no período selecionado.";
          return;
        }
  
        const labels = dados.map(d => d.datahora_completa);
        const valores = dados.map(d => parseFloat(d.aqi));
  
        const ctx = canvas.getContext("2d");
        new Chart(ctx, {
          type: "line",
          data: {
            labels: labels,
            datasets: [{
              label: "Índice de Qualidade do Ar (AQI)",
              data: valores,
              borderColor: "rgba(30, 164, 78, 1)",
              backgroundColor: "rgba(30, 164, 78, 0.2)",
              fill: true,
              tension: 0.3,
              pointRadius: 3
            }]
          },
          options: {
            responsive: true,
            scales: {
              x: {
                title: { display: true, text: "Data e Hora" },
                ticks: { autoSkip: true, maxTicksLimit: 10 }
              },
              y: {
                title: { display: true, text: "AQI" },
                beginAtZero: true
              }
            },
            plugins: {
              legend: { display: true, position: "top" },
              title: { display: true, text: "Evolução dos valores de AQI (≥4)" }
            }
          }
        });
      })
      .catch(() => {
        loading.textContent = "Erro ao carregar dados.";
      });
  });
  