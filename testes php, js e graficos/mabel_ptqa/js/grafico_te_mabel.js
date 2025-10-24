document.addEventListener('DOMContentLoaded', () => {
const form = document.getElementById('form-filtro');
const ctx_te = document.getElementById('grafico_te').getContext('2d');
let grafico_te;

async function carregarTE(inicio, fim) {
  const url = `consulta_te_mabel.php?inicio=${inicio}&fim=${fim}&formato=json`;
  const resposta = await fetch(url);
  const dados = await resposta.json();

  const labels = dados.map(l => l.datahora);
  const valores = dados.map(l => parseFloat(l.te));

  if (grafico_te) grafico_te.destroy();

  grafico_te = new Chart(ctx_te, {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'Temperatura Externa (°C)',
        data: valores,
        borderColor: 'rgb(54, 162, 235)',
        backgroundColor: 'rgba(54, 162, 235, 0.2)',
        fill: true,
        tension: 0.3
      }]
    },
    options: { responsive: true }
  });
}

form.addEventListener('submit', e => {
  e.preventDefault();
  const inicio = document.getElementById('inicio').value;
  const fim = document.getElementById('fim').value;
  carregarTE(inicio, fim);
});

// carrega ao abrir
carregarTE('2025-06-01', '2025-06-30');
});

