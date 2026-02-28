function startCountdown(endTime) {

    const countdownElement = document.getElementById("countdown");

    function updateCountdown() {
        const now = new Date().getTime();
        const distance = new Date(endTime).getTime() - now;

        if (distance <= 0) {
            countdownElement.innerHTML = "Voting Closed";
            clearInterval(interval);
            return;
        }

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        countdownElement.innerHTML =
            days + "d " + hours + "h " +
            minutes + "m " + seconds + "s ";
    }

    const interval = setInterval(updateCountdown, 1000);
}