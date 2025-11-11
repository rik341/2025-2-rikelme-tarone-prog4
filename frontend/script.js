document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("formPeriodo");
  const ctx = document.getElementById("graficoTemperatura").getContext("2d");
  const valorMedia = document.getElementById("valorMedia");
  const URL_GRAFICO = "/2025-2-rikelme-tarone-prog4/backend/consultas_mabel/consulta_te_mabel.php";
  const URL_MEDIA = "/2025-2-rikelme-tarone-prog4/backend/consultas_mabel/consulta_media_te_mabel.php";
  let chart; // referência ao gráfico para atualizar depois

  // Função que busca os dados e desenha o gráfico
  async function carregarGrafico(inicio, fim) {
    try {
      const url = `${URL_GRAFICO}?formato=json&inicio=${inicio}&fim=${fim}`;
      const resposta = await fetch(url);
      if (!resposta.ok) throw new Error("Erro ao carregar dados do gráfico");
      const dados = await resposta.json();

      // Extrai rótulos (datas) e valores (temperaturas)
      const labels = dados.map(item => item.datahora_completa);
      const valores = dados.map(item => parseFloat(item.te));

      // Se já existe um gráfico anterior, destrói antes de redesenhar
      if (chart) chart.destroy();

      chart = new Chart(ctx, {
        type: "line",
        data: {
          labels,
          datasets: [{
            label: "Temperatura Externa (°C)",
            data: valores,
            borderColor: "rgba(75, 192, 192, 1)",
            borderWidth: 2,
            tension: 0.3,
            fill: false,
            pointRadius: 2
          }]
        },
        options: {
          responsive: true,
          scales: {
            x: {
              ticks: { autoSkip: true, maxRotation: 45, minRotation: 45 },
              title: { display: true, text: "Data e Hora" }
            },
            y: {
              beginAtZero: false,
              title: { display: true, text: "Temperatura (°C)" }
            }
          }
        }
      });
    } catch (erro) {
      console.error("Erro ao carregar gráfico:", erro);
      alert("Não foi possível carregar o gráfico.");
    }
  }

  // --- BLOCO PARA BUSCAR A MÉDIA ---
  async function carregarMedia(inicio, fim) {
    try {
      const resposta = await fetch(`${URL_MEDIA}?formato=json&inicio=${inicio}&fim=${fim}`);
      if (!resposta.ok) throw new Error("Erro ao carregar média");
      const dados = await resposta.json();
      valorMedia.textContent = dados.media_temperatura_externa
        ? dados.media_temperatura_externa
        : "--";
    } catch (erro) {
      console.error("Erro ao carregar média:", erro);
      valorMedia.textContent = "--";
    }
  }

  // Ao enviar o formulário, atualiza o gráfico e a média
  form.addEventListener("submit", e => {
    e.preventDefault();
    const inicio = document.getElementById("inicio").value;
    const fim = document.getElementById("fim").value;
    if (!inicio || !fim) {
      alert("Selecione as duas datas para gerar o gráfico.");
      return;
    }
    carregarGrafico(inicio, fim);
    carregarMedia(inicio, fim);
  });

  // Define um período inicial padrão (últimos 7 dias)
  const hoje = new Date();
  const seteDiasAtras = new Date();
  seteDiasAtras.setDate(hoje.getDate() - 7);
  document.getElementById("fim").value = hoje.toISOString().split("T")[0];
  document.getElementById("inicio").value = seteDiasAtras.toISOString().split("T")[0];

  // Carrega gráfico e média iniciais
  carregarGrafico(
    document.getElementById("inicio").value,
    document.getElementById("fim").value
  );
  carregarMedia(
    document.getElementById("inicio").value,
    document.getElementById("fim").value
  );
});
