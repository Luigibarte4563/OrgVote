function validateVoteForm() {
    const radios = document.getElementsByName("candidate_id");

    let selected = false;

    for (let i = 0; i < radios.length; i++) {
        if (radios[i].checked) {
            selected = true;
            break;
        }
    }

    if (!selected) {
        alert("Please select a candidate before submitting.");
        return false;
    }

    return confirm("Are you sure you want to submit your vote?");
}