const currentURL = window.location.href;
const roomId = currentURL.split("/").pop();
let gameData = null;
let turn = false;

  // dom is ready
  document.addEventListener('DOMContentLoaded', function() {

    refreshGame();

    // get the button
    const button = document.querySelector('.doodle-button');

    // get the modal
    const modal = document.querySelector('.modal');
    /** Show the modal */
    function showModal() {
      const modal = document.querySelector('.modal');

      modal.style.opacity = '1';
      modal.classList.add('active');
    }

    /** Hide the modal */
    function hideModal() {
      const modal = document.querySelector('.modal');

      // modal.style.opacity = '0';
      modal.classList.remove('active');
    }

    // get the close button
    const close = document.querySelector('.modal-action-close');

    // add event listener
    button.addEventListener('click', showModal);
    close.addEventListener('click', hideModal);

    // get the send button
    const send = document.querySelector('#send');

    // add event listener
    send.addEventListener('click', function() {
      const proposal = document.querySelector('#proposal').value;

      // send the proposal to the server
      makeProposal(proposal);

      // hide the modal
      hideModal();
    });

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
                  refreshGame();
                  gameWon();
              } else if (data.isOver) {
                  refreshGame();
                  gameOver();
              } else {
                  refreshGame();
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
      const json = null; 
      return $.get({
          url: '/api/room/' + roomId + '/game/latest/participant',
          dataType: 'json'
      }).then(function(data, status) {
          if (status !== 'success') {
              console.log('Error: ' + status);
              return;
          } else if (data === null) {
              console.log('No game data found');
              // show waiting screen
              return;
          } else {
              console.log('Game data found');
              gameData = data;
              // load the game data
              console.log('data', data);
              return data;
          }
      });
  }

  async function refreshGame(){
      const json = await getClassicGameState(roomId);
      // get balise word from html
      const word = document.querySelector('.word');

      // get json wordStatus
      const wordStatus = json.wordStatus;
      
      // split wordStatus with space
      const wordStatusSplit = wordStatus.split('');

      // replace word with wordStatusSplit
      word.innerHTML = wordStatusSplit.join(' ');
      console.log('vava',wordStatusSplit.join(' '));

      // get points
      const points = document.querySelector('.score');
      // get json points
      const pointsStatus = json.points;

      // adding points with pointsStatus
      // parse points inner html to int
      points.innerHTML = pointsStatus;

      // get lives
      const lives = document.querySelector('.attempts');
      // get json lives
      const livesStatus = json.attempts;
      // parse lives inner html to int
      lives.innerHTML = livesStatus;

      // if lives === 7 call game over
      if (livesStatus >= 7) {
          gameOver();
      }

      // if won call game won
      // if wordStatusSPlit doesnt contain _ call game won
      if (!wordStatusSplit.includes('_')) {
          gameWon();
      }


      // Refresh game elements
  }

  function gameOver() {
      // disable button make proposal
      const button = document.querySelector('.doodle-button');
      // hide button make proposal
      button.style.display = 'none';

      // add a button back to home
      const backHome = document.querySelector('.back-home');
      backHome.classList.add('active');

      // modal game over
      const modal = document.querySelector('.modal');

      // show modal for 5 seconds and then hide it
      modal.style.opacity = '1';

      // add class active to modal
      modal.classList.add('active');

      // get modal content
      const modalContent = document.querySelector('.modal-content');

      // add text to modal content
      modalContent.innerHTML = 'Game Over';

      // hide modal after 5 seconds
      setTimeout(function() {
          modal.style.opacity = '0';
          modal.classList.remove('active');
      } , 5000);

  }

  function gameWon() {
    // disable button make proposal
    const button = document.querySelector('.doodle-button');
    // hide button make proposal
    button.style.display = 'none';

    // add a button back to home
    const backHome = document.querySelector('.back-home');
    backHome.classList.add('active');

    // modal win
    const modal = document.querySelector('.modal');

    // show modal for 5 seconds and then hide it
    modal.style.opacity = '1';

    // add class active to modal
    modal.classList.add('active');

    // get modal content
    const modalContent = document.querySelector('.modal-content');

    // add text to modal content
    modalContent.innerHTML = 'You have Won';

    // hide modal after 5 seconds
    setTimeout(function() {
        modal.style.opacity = '0';
        modal.classList.remove('active');
    } , 5000);
  }

});
