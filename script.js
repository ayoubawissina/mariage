
// Compte à rebours jusqu'au mariage
const countdown = document.getElementById("countdown");
const dateMariage = new Date("2025-09-19T16:30:00").getTime();

const timer = setInterval(() => {
  const now = new Date().getTime();
  const distance = dateMariage - now;

  const jours = Math.floor(distance / (1000 * 60 * 60 * 24));
  const heures = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
  const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
  const secondes = Math.floor((distance % (1000 * 60)) / 1000);

  countdown.innerHTML = `⏳ ${jours}j ${heures}h ${minutes}m ${secondes}s`;

  if (distance < 0) {
    clearInterval(timer);
    countdown.innerHTML = "💍 C’est le grand jour !";
  }
}, 1000);
