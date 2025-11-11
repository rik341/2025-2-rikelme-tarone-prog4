window.addEventListener("DOMContentLoaded", () => {
    const loading = document.getElementById("loading");
    const canvasBaixa = document.getElementById("graficoPressaoBaixa");
    const canvasMinima = document.getElementById("graficoPressaoMinima");
  
    // Busca JSON com os dados
    fetch(`pressao_ptqa.php?inicio=${dataInicial}&fim=${dataFinal}&formato=json`)
        .then(res => res.json())
        .then(dados => {
            loading.style.display = "none";
  
            // ------------------ Gráfico 1 - Pressão < 1000 ------------------
            if (dados.pressao_baixa && dados.pressao_baixa.length > 0) {
                const labels1 = dados.pressao_baixa.map(d => d.datahora_completa);
                const valores1 = dados.pressao_baixa.map(d => parseFloat(d.pressao));
  
                new Chart(canvasBaixa, {
                    type: "line",
                    data: {
                        labels: labels1,
                        datasets: [{
                            label: "Pressão < 1000 hPa",
                            data: valores1,
                            borderColor: "rgba(255, 99, 132, 1)",
                            backgroundColor: "rgba(255, 99, 132, 0.2)",
                            fill: true,
                            tension: 0.3,
                            pointRadius: 3
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            x: { title: { display: true, text: "Data e Hora" } },
                            y: { title: { display: true, text: "Pressão (hPa)" }, beginAtZero: false }
                        },
                        plugins: {
                            title: { display: true, text: "Registros com pressão < 1000 hPa" },
                            legend: { display: true, position: "top" }
                        }
                    }
                });
            }
  
            // ------------------ Gráfico 2 - Pressão mínima diária ------------------
            if (dados.pressao_minima && dados.pressao_minima.length > 0) {
                const labels2 = dados.pressao_minima.map(d => d.dataleitura);
                const valores2 = dados.pressao_minima.map(d => parseFloat(d.pressao_minima));
  
                new Chart(canvasMinima, {
                    type: "bar",
                    data: {
                        labels: labels2,
                        datasets: [{
                            label: "Pressão Mínima Diária",
                            data: valores2,
                            backgroundColor: "rgba(30, 164, 78, 0.7)",
                            borderColor: "rgba(30, 164, 78, 1)",
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            x: { title: { display: true, text: "Data" } },
                            y: { title: { display: true, text: "Pressão (hPa)" }, beginAtZero: false }
                        },
                        plugins: {
                            title: { display: true, text: "Mínima Pressão Registrada por Dia" },
                            legend: { display: true, position: "top" }
                        }
                    }
                });
            }
  
            // Caso não haja dados
            if ((!dados.pressao_baixa || dados.pressao_baixa.length === 0) &&
                (!dados.pressao_minima || dados.pressao_minima.length === 0)) {
                loading.style.display = "block";
                loading.textContent = "Nenhum dado encontrado no período selecionado.";
            }
        })
        .catch(() => {
            loading.textContent = "Erro ao carregar dados.";
        });
  });
  