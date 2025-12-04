window.addEventListener("DOMContentLoaded", () => {

    const loading = document.getElementById("loading");
    const canvasBaixa = document.getElementById("graficoPressaoBaixa");
    const canvasMinima = document.getElementById("graficoPressaoMinima");

    // pega intervalo digitado (somente para o 1º gráfico)
    const intervalo = parseInt(document.getElementById("intervalo")?.value) || 20;

    fetch(`pressao_ptqa.php?inicio=${dataInicial}&fim=${dataFinal}&formato=json`)
        .then(res => res.json())
        .then(dados => {

            loading.style.display = "none";

            /* =========================================
                GRÁFICO 1 — PRESSÃO < 1000
            ========================================== */
            if (dados.pressao_baixa && dados.pressao_baixa.length > 0) {

                const labelsBruto1 = dados.pressao_baixa.map(d => d.datahora_completa);
                const valoresBruto1 = dados.pressao_baixa.map(d => Number(d.pressao));

                // aplica o intervalo SOMENTE NO GRÁFICO 1
                const labels1 = labelsBruto1.filter((_, i) => i % intervalo === 0);
                const valores1 = valoresBruto1.filter((_, i) => i % intervalo === 0);

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
                            pointRadius: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            x: { title: { display: true, text: "Data e Hora" } },
                            y: { title: { display: true, text: "Pressão (hPa)" } }
                        }
                    }
                });
            }

            /* =========================================
                GRÁFICO 2 — PRESSÃO MÍNIMA DIÁRIA
            ========================================== */
            if (dados.pressao_minima && dados.pressao_minima.length > 0) {

                // AQUI → usa TODOS os dados, SEM intervalo
                const labels2 = dados.pressao_minima.map(d => d.data_leitura);
                const valores2 = dados.pressao_minima.map(d => Number(d.pressao_minima));

                const minVal = Math.min(...valores2);
                const maxVal = Math.max(...valores2);

                new Chart(canvasMinima, {
                    type: "bar",
                    data: {
                        labels: labels2,
                        datasets: [{
                            label: "Pressão mínima diária",
                            data: valores2,
                            backgroundColor: "rgba(30, 164, 78, 0.7)",
                            borderColor: "rgba(30, 164, 78, 1)",
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            x: {
                                title: { display: true, text: "Data" }
                            },
                            y: {
                                title: { display: true, text: "Pressão (hPa)" },
                                min: minVal - 2,
                                max: maxVal + 2,
                                ticks: { stepSize: 2 }
                            }
                        }
                    }
                });
            }

        })
        .catch(() => {
            loading.textContent = "Erro ao carregar dados.";
        });
});
