const currentURL = window.location.href;
const roomId = currentURL.split("/").pop();
let gameData = null;
let turn = false;

function loadGameData(gameData) {

}

/***
 * Make a proposal to the server
 * @param proposal
 */
function makeProposal(proposal) {
    // Show modal to ask for letter or word proposal
    $.post({
        url: '/api/room/' + roomId + '/game/proposal',
        data: {
            proposal: proposal
        },
        dataType: 'json'
    }).then(function(data, status) {
        if (status !== 'success') {
            console.log('Error: ' + status);
            return null;
        } else {
            console.log('Proposal sent');
            if (data.isWon) {
                gameWon();
            }
        //     // Update animation based on the response
        //     // Repeat until game is over
        }
    });
    // Update animation based on the response
    // Repeat until game is over
}

function getGame(roomId) {
    $.get({
        url: '/api/room/' + roomId + '/game/latest',
        dataType: 'json'
    }).then(function(data, status) {
        if (status !== 'success') {
            console.log('Error: ' + status);
            return null;
        }else if (data === null) {
            console.log('No game data found');
            // show waiting screen
            return null;
        } else {
            console.log(data);
            return data;
        }
    });
}

function getClassicGameState(roomId){
    $.get({
        url: '/api/room/' + roomId + '/game/latest/participant',
        dataType: 'json'
    }).then(function(data, status) {
        if (status !== 'success') {
            console.log('Error: ' + status);
            return;
        }else if (data === null) {
            console.log('No game data found');
            // show waiting screen
            return;
        } else {
            console.log('Game data found');
            gameData = data;
            // load the game data
            console.log(data);
        }
    });
}

function refreshGame(){
    // Refresh game elements
}

function gameOver() {
    // Show game over screen
}

function gameWon() {
    // Show game won screen
}


