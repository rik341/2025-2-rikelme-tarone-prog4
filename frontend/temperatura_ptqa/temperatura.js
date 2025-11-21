document.addEventListener("DOMContentLoaded", async () => {

    const url = `temperature.php?formato=json&inicio=${dataInicial}&fim=${dataFinal}`;
    const loading = document.getElementById("loading");

    /* =============================
       1) BUSCAR DADOS DO PHP
       ============================= */
    let resp;
    try {
        resp = await fetch(url);

        if (!resp.ok) {
            loading.textContent = "Erro ao buscar dados do servidor.";
            return;
        }
    } catch (e) {
        loading.textContent = "Erro de conexão com o servidor.";
        return;
    }

    const dados = await resp.json();
    loading.style.display = "none";

    /* =============================
       2) GRÁFICO DE TEMPERATURA
       ============================= */
    if (dados.lista.length === 0) {
        document.getElementById("graficoTemperatura").outerHTML =
            "<p>Nenhum dado de temperatura encontrado no período.</p>";
    } else {
        const labelsTemp = dados.lista.map(t => `${t.data} ${t.hora}`);
        const valoresTemp = dados.lista.map(t => Number(t.temperatura));

        new Chart(document.getElementById("graficoTemperatura"), {
            type: "line",
            data: {
                labels: labelsTemp,
                datasets: [{
                    label: "Temperatura (°C)",
                    data: valoresTemp,
                    borderColor: "orange",
                    borderWidth: 2,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                scales: { y: { beginAtZero: false } }
            }
        });
    }

    /* =============================
       3) TEMPERATURA MÉDIA
       ============================= */
    const media = dados.media_periodo?.temperatura_media;
    document.getElementById("valorMedia").textContent =
        media ? `${Number(media).toFixed(2)} °C` : "Sem dados no período";

    /* =============================
       4) GRÁFICO MÁX / MIN / MÉD
       ============================= */
    if (dados.max_min_med) {
        new Chart(document.getElementById("graficoMaxMinMed"), {
            type: "bar",
            data: {
                labels: ["Máxima", "Mínima", "Média"],
                datasets: [{
                    label: "Temperatura (°C)",
                    data: [
                        dados.max_min_med.temp_maxima,
                        dados.max_min_med.temp_minima,
                        Number(dados.max_min_med.temp_media).toFixed(2)
                    ],
                    backgroundColor: ["red", "blue", "green"]
                }]
            },
            options: {
                responsive: true,
                scales: { y: { beginAtZero: false } }
            }
        });
    }

    /* =============================
       5) GRÁFICO UMIDADE > 70%
       ============================= */
    if (dados.umidade_alta.length === 0) {
        document.getElementById("graficoUmidade").outerHTML =
            "<p>Nenhum registro de umidade acima de 70%.</p>";
    } else {
        const labelsUmidade = dados.umidade_alta.map(u => `${u.data} ${u.hora}`);
        const valoresUmidade = dados.umidade_alta.map(u => Number(u.umidade));

        new Chart(document.getElementById("graficoUmidade"), {
            type: "bar",
            data: {
                labels: labelsUmidade,
                datasets: [{
                    label: "Umidade (%)",
                    data: valoresUmidade,
                    backgroundColor: "rgba(54,162,235,0.7)",
                    borderColor: "blue",
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                scales: { y: { beginAtZero: true } }
            }
        });
    }

});
