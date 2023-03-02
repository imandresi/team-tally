
function editTeamsAddListBtn() {
    const newTeamBtnEl = document.querySelector('.teamtally__teams__edit_team h1.wp-heading-inline');

    if (!newTeamBtnEl) {
        return;
    }

    const btnUrl = document.querySelector('.teamtally__teams__edit_team #teams_list_url').value;

    if (btnUrl) {
        const listTeamsBtnEl = document.createElement('a');
        listTeamsBtnEl.setAttribute('class', 'page-title-action');
        listTeamsBtnEl.setAttribute('href', btnUrl);
        listTeamsBtnEl.setAttribute('style', "margin-left: 20px;")
        listTeamsBtnEl.textContent = "List Teams";
        newTeamBtnEl.append(listTeamsBtnEl);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    editTeamsAddListBtn();
});