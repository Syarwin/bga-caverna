{OVERALL_GAME_HEADER}

<div id="floating-building-boards-wrapper">
  <div id="floating-building-buttons"></div>
  <div id="floating-building-slider">
    <div id="floating-building-boards-container">
      <div id="buildings-container"></div>
    </div>
  </div>
</div>



<div id="position-wrapper">
  <div id="central-board-wrapper"><div id="central-board"></div></div>
</div>


<svg style="display:none" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="map-marker-question" role="img" xmlns="http://www.w3.org/2000/svg">
  <symbol id="help-marker-svg" viewBox="0 0 512 512"><g class="fa-group"><path class="fa-secondary" fill="white" d="M256 8C119 8 8 119.08 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm0 422a46 46 0 1 1 46-46 46.05 46.05 0 0 1-46 46zm40-131.33V300a12 12 0 0 1-12 12h-56a12 12 0 0 1-12-12v-4c0-41.06 31.13-57.47 54.65-70.66 20.17-11.31 32.54-19 32.54-34 0-19.82-25.27-33-45.7-33-27.19 0-39.44 13.14-57.3 35.79a12 12 0 0 1-16.67 2.13L148.82 170a12 12 0 0 1-2.71-16.26C173.4 113 208.16 90 262.66 90c56.34 0 116.53 44 116.53 102 0 77-83.19 78.21-83.19 106.67z" opacity="1"></path><path class="fa-primary" fill="currentColor" d="M256 338a46 46 0 1 0 46 46 46 46 0 0 0-46-46zm6.66-248c-54.5 0-89.26 23-116.55 63.76a12 12 0 0 0 2.71 16.24l34.7 26.31a12 12 0 0 0 16.67-2.13c17.86-22.65 30.11-35.79 57.3-35.79 20.43 0 45.7 13.14 45.7 33 0 15-12.37 22.66-32.54 34C247.13 238.53 216 254.94 216 296v4a12 12 0 0 0 12 12h56a12 12 0 0 0 12-12v-1.33c0-28.46 83.19-29.67 83.19-106.67 0-58-60.19-102-116.53-102z"></path></g>
  </symbol>
</svg>


<svg style="display:none" aria-hidden="true" focusable="false" data-prefix="far" data-icon="expand-alt" role="img" xmlns="http://www.w3.org/2000/svg">
  <symbol id="expand-marker-svg" viewBox="0 0 320 512">
    <path fill="currentColor" d="M177 159.7l136 136c9.4 9.4 9.4 24.6 0 33.9l-22.6 22.6c-9.4 9.4-24.6 9.4-33.9 0L160 255.9l-96.4 96.4c-9.4 9.4-24.6 9.4-33.9 0L7 329.7c-9.4-9.4-9.4-24.6 0-33.9l136-136c9.4-9.5 24.6-9.5 34-.1z"></path>
  </symbol>
</svg>


<svg style="display:none" aria-hidden="true" focusable="false" data-prefix="far" data-icon="search-plus" role="img" xmlns="http://www.w3.org/2000/svg">
  <symbol id="zoom-svg" viewBox="0 0 512 512">
    <path fill="currentColor" d="M312 196v24c0 6.6-5.4 12-12 12h-68v68c0 6.6-5.4 12-12 12h-24c-6.6 0-12-5.4-12-12v-68h-68c-6.6 0-12-5.4-12-12v-24c0-6.6 5.4-12 12-12h68v-68c0-6.6 5.4-12 12-12h24c6.6 0 12 5.4 12 12v68h68c6.6 0 12 5.4 12 12zm196.5 289.9l-22.6 22.6c-4.7 4.7-12.3 4.7-17 0L347.5 387.1c-2.3-2.3-3.5-5.3-3.5-8.5v-13.2c-36.5 31.5-84 50.6-136 50.6C93.1 416 0 322.9 0 208S93.1 0 208 0s208 93.1 208 208c0 52-19.1 99.5-50.6 136h13.2c3.2 0 6.2 1.3 8.5 3.5l121.4 121.4c4.7 4.7 4.7 12.3 0 17zM368 208c0-88.4-71.6-160-160-160S48 119.6 48 208s71.6 160 160 160 160-71.6 160-160z"></path>
  </symbol>
</svg>

<script type="text/javascript">
var jstpl_configPlayerBoard = `
<div class='player-board' id="player_board_config">
  <div id="player_config" class="player_board_content">

    <div id="player_config_row">
      <div id="uwe-help"></div>
      <div id="help-mode-switch">
        <input type="checkbox" class="checkbox" id="help-mode-chk" />
        <label class="label" for="help-mode-chk">
          <div class="ball"></div>
        </label>

        <svg aria-hidden="true" focusable="false" data-prefix="fad" data-icon="question-circle" class="svg-inline--fa fa-question-circle fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><g class="fa-group"><path class="fa-secondary" fill="currentColor" d="M256 8C119 8 8 119.08 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm0 422a46 46 0 1 1 46-46 46.05 46.05 0 0 1-46 46zm40-131.33V300a12 12 0 0 1-12 12h-56a12 12 0 0 1-12-12v-4c0-41.06 31.13-57.47 54.65-70.66 20.17-11.31 32.54-19 32.54-34 0-19.82-25.27-33-45.7-33-27.19 0-39.44 13.14-57.3 35.79a12 12 0 0 1-16.67 2.13L148.82 170a12 12 0 0 1-2.71-16.26C173.4 113 208.16 90 262.66 90c56.34 0 116.53 44 116.53 102 0 77-83.19 78.21-83.19 106.67z" opacity="0.4"></path><path class="fa-primary" fill="currentColor" d="M256 338a46 46 0 1 0 46 46 46 46 0 0 0-46-46zm6.66-248c-54.5 0-89.26 23-116.55 63.76a12 12 0 0 0 2.71 16.24l34.7 26.31a12 12 0 0 0 16.67-2.13c17.86-22.65 30.11-35.79 57.3-35.79 20.43 0 45.7 13.14 45.7 33 0 15-12.37 22.66-32.54 34C247.13 238.53 216 254.94 216 296v4a12 12 0 0 0 12 12h56a12 12 0 0 0 12-12v-1.33c0-28.46 83.19-29.67 83.19-106.67 0-58-60.19-102-116.53-102z"></path></g></svg>
      </div>
    </div>

    <div id="player_config_row">
      <div id="show-scores">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
          <g class="fa-group">
            <path class="fa-secondary" fill="currentColor" d="M0 192v272a48 48 0 0 0 48 48h352a48 48 0 0 0 48-48V192zm324.13 141.91a11.92 11.92 0 0 1-3.53 6.89L281 379.4l9.4 54.6a12 12 0 0 1-17.4 12.6l-49-25.8-48.9 25.8a12 12 0 0 1-17.4-12.6l9.4-54.6-39.6-38.6a12 12 0 0 1 6.6-20.5l54.7-8 24.5-49.6a12 12 0 0 1 21.5 0l24.5 49.6 54.7 8a12 12 0 0 1 10.13 13.61zM304 128h32a16 16 0 0 0 16-16V16a16 16 0 0 0-16-16h-32a16 16 0 0 0-16 16v96a16 16 0 0 0 16 16zm-192 0h32a16 16 0 0 0 16-16V16a16 16 0 0 0-16-16h-32a16 16 0 0 0-16 16v96a16 16 0 0 0 16 16z" opacity="0.4"></path>
            <path class="fa-primary" fill="currentColor" d="M314 320.3l-54.7-8-24.5-49.6a12 12 0 0 0-21.5 0l-24.5 49.6-54.7 8a12 12 0 0 0-6.6 20.5l39.6 38.6-9.4 54.6a12 12 0 0 0 17.4 12.6l48.9-25.8 49 25.8a12 12 0 0 0 17.4-12.6l-9.4-54.6 39.6-38.6a12 12 0 0 0-6.6-20.5zM400 64h-48v48a16 16 0 0 1-16 16h-32a16 16 0 0 1-16-16V64H160v48a16 16 0 0 1-16 16h-32a16 16 0 0 1-16-16V64H48a48 48 0 0 0-48 48v80h448v-80a48 48 0 0 0-48-48z"></path>
          </g>
        </svg>
      </div>

      <div id="show-help">
      <svg  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
        <g>
          <path d="m 233.2661,19.969492 -65.3,132.399998 -146.099998,21.3 c -26.2000003,3.8 -36.7,36.1 -17.7000003,54.6 l 105.6999983,103 -24.999998,145.5 c -4.5,26.3 23.199998,46 46.399998,33.7 l 130.7,-68.7 130.7,68.7 c 23.2,12.2 50.9,-7.4 46.4,-33.7 l -25,-145.5 105.7,-103 c 19,-18.5 8.5,-50.8 -17.7,-54.6 l -146.1,-21.3 -65.3,-132.399998 c -11.7,-23.6000005 -45.6,-23.9000005 -57.4,0 z"
             style="fill:currentColor;fill-opacity:0.4;stroke-width:0.50514001" />
          <path style="fill:currentColor"
             d="m 266.54954,154.03389 c -41.43656,0 -68.27515,16.07436 -89.34619,44.74159 -3.82236,5.20034 -2.64393,12.33041 2.68806,16.15841 l 22.3943,16.0773 c 5.38496,3.86585 13.04682,2.96194 17.26269,-2.03884 13.00373,-15.42456 22.64971,-24.30544 42.96178,-24.30544 15.97056,0 35.72456,9.73171 35.72456,24.39489 0,11.08489 -9.66467,16.77774 -25.43382,25.14841 -18.3892,9.7617 -42.72402,21.91024 -42.72402,52.30077 v 4.81105 c 0,6.51516 5.57808,11.7966 12.45917,11.7966 h 37.62199 c 6.88108,0 12.45915,-5.28144 12.45915,-11.7966 v -2.83758 c 0,-21.06678 65.03059,-21.94415 65.03059,-78.95226 5.2e-4,-42.93179 -47.03384,-75.4983 -91.09826,-75.4983 z m -5.20222,183.56459 c -19.82875,0 -35.96076,15.27415 -35.96076,34.04846 0,18.77381 16.13201,34.04797 35.96076,34.04797 19.82875,0 35.96077,-15.27416 35.96077,-34.04846 0,-18.7743 -16.13202,-34.04797 -35.96077,-34.04797 z" />
        </g>
      </svg>
      </div>

      <div id="show-settings">
        <svg  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
          <g>
            <path class="fa-secondary" fill="currentColor" d="M638.41 387a12.34 12.34 0 0 0-12.2-10.3h-16.5a86.33 86.33 0 0 0-15.9-27.4L602 335a12.42 12.42 0 0 0-2.8-15.7 110.5 110.5 0 0 0-32.1-18.6 12.36 12.36 0 0 0-15.1 5.4l-8.2 14.3a88.86 88.86 0 0 0-31.7 0l-8.2-14.3a12.36 12.36 0 0 0-15.1-5.4 111.83 111.83 0 0 0-32.1 18.6 12.3 12.3 0 0 0-2.8 15.7l8.2 14.3a86.33 86.33 0 0 0-15.9 27.4h-16.5a12.43 12.43 0 0 0-12.2 10.4 112.66 112.66 0 0 0 0 37.1 12.34 12.34 0 0 0 12.2 10.3h16.5a86.33 86.33 0 0 0 15.9 27.4l-8.2 14.3a12.42 12.42 0 0 0 2.8 15.7 110.5 110.5 0 0 0 32.1 18.6 12.36 12.36 0 0 0 15.1-5.4l8.2-14.3a88.86 88.86 0 0 0 31.7 0l8.2 14.3a12.36 12.36 0 0 0 15.1 5.4 111.83 111.83 0 0 0 32.1-18.6 12.3 12.3 0 0 0 2.8-15.7l-8.2-14.3a86.33 86.33 0 0 0 15.9-27.4h16.5a12.43 12.43 0 0 0 12.2-10.4 112.66 112.66 0 0 0 .01-37.1zm-136.8 44.9c-29.6-38.5 14.3-82.4 52.8-52.8 29.59 38.49-14.3 82.39-52.8 52.79zm136.8-343.8a12.34 12.34 0 0 0-12.2-10.3h-16.5a86.33 86.33 0 0 0-15.9-27.4l8.2-14.3a12.42 12.42 0 0 0-2.8-15.7 110.5 110.5 0 0 0-32.1-18.6A12.36 12.36 0 0 0 552 7.19l-8.2 14.3a88.86 88.86 0 0 0-31.7 0l-8.2-14.3a12.36 12.36 0 0 0-15.1-5.4 111.83 111.83 0 0 0-32.1 18.6 12.3 12.3 0 0 0-2.8 15.7l8.2 14.3a86.33 86.33 0 0 0-15.9 27.4h-16.5a12.43 12.43 0 0 0-12.2 10.4 112.66 112.66 0 0 0 0 37.1 12.34 12.34 0 0 0 12.2 10.3h16.5a86.33 86.33 0 0 0 15.9 27.4l-8.2 14.3a12.42 12.42 0 0 0 2.8 15.7 110.5 110.5 0 0 0 32.1 18.6 12.36 12.36 0 0 0 15.1-5.4l8.2-14.3a88.86 88.86 0 0 0 31.7 0l8.2 14.3a12.36 12.36 0 0 0 15.1 5.4 111.83 111.83 0 0 0 32.1-18.6 12.3 12.3 0 0 0 2.8-15.7l-8.2-14.3a86.33 86.33 0 0 0 15.9-27.4h16.5a12.43 12.43 0 0 0 12.2-10.4 112.66 112.66 0 0 0 .01-37.1zm-136.8 45c-29.6-38.5 14.3-82.5 52.8-52.8 29.59 38.49-14.3 82.39-52.8 52.79z" opacity="0.4"></path>
            <path class="fa-primary" fill="currentColor" d="M420 303.79L386.31 287a173.78 173.78 0 0 0 0-63.5l33.7-16.8c10.1-5.9 14-18.2 10-29.1-8.9-24.2-25.9-46.4-42.1-65.8a23.93 23.93 0 0 0-30.3-5.3l-29.1 16.8a173.66 173.66 0 0 0-54.9-31.7V58a24 24 0 0 0-20-23.6 228.06 228.06 0 0 0-76 .1A23.82 23.82 0 0 0 158 58v33.7a171.78 171.78 0 0 0-54.9 31.7L74 106.59a23.91 23.91 0 0 0-30.3 5.3c-16.2 19.4-33.3 41.6-42.2 65.8a23.84 23.84 0 0 0 10.5 29l33.3 16.9a173.24 173.24 0 0 0 0 63.4L12 303.79a24.13 24.13 0 0 0-10.5 29.1c8.9 24.1 26 46.3 42.2 65.7a23.93 23.93 0 0 0 30.3 5.3l29.1-16.7a173.66 173.66 0 0 0 54.9 31.7v33.6a24 24 0 0 0 20 23.6 224.88 224.88 0 0 0 75.9 0 23.93 23.93 0 0 0 19.7-23.6v-33.6a171.78 171.78 0 0 0 54.9-31.7l29.1 16.8a23.91 23.91 0 0 0 30.3-5.3c16.2-19.4 33.7-41.6 42.6-65.8a24 24 0 0 0-10.5-29.1zm-151.3 4.3c-77 59.2-164.9-28.7-105.7-105.7 77-59.2 164.91 28.7 105.71 105.7z"></path>
          </g>
        </svg>
      </div>
    </div>
  </div>
</div>
`;



var jstpl_scoresModal = `
<table id='players-scores'>
  <thead>
    <tr id="scores-headers">
      <th></th><th></th>
    </tr>
  </thead>
  <tbody id="scores-body">
    <tr id="scores-row-dog">
      <td class="row-header">\${dog}</td><td><DOG></td>
    </tr>
    <tr id="scores-row-sheep">
      <td class="row-header">\${sheep}</td><td><SHEEP></td>
    </tr>
    <tr id="scores-row-pig">
      <td class="row-header">\${pig}</td><td><PIG></td>
    </tr>
    <tr id="scores-row-cattle">
      <td class="row-header">\${cattle}</td><td><CATTLE></td>
    </tr>
    <tr id="scores-row-donkey">
      <td class="row-header">\${donkey}</td><td><DONKEY></td>
    </tr>
    <tr id="scores-row-grains">
      <td class="row-header">\${grains}</td><td><GRAIN></td>
    </tr>
    <tr id="scores-row-vegetables">
      <td class="row-header">\${vegetables}</td><td><VEGETABLE></td>
    </tr>
    <tr id="scores-row-rubies">
      <td class="row-header">\${rubies}</td><td><RUBY></td>
    </tr>
    <tr id="scores-row-dwarfs">
      <td class="row-header">\${dwarfs}</td><td><DWARF></td>
    </tr>
    <tr id="scores-row-empty">
      <td class="row-header">\${empty}</td><td><EMPTY></td>
    </tr>
    <tr id="scores-row-pastures">
      <td class="row-header">\${pastures}</td><td><PASTURE></td>
    </tr>
    <tr id="scores-row-mines">
      <td class="row-header">\${mines}</td><td><MINES></td>
    </tr>
    <tr id="scores-row-buildings">
      <td class="row-header">\${buildings}</td><td><BUILDING></td>
    </tr>
    <tr id="scores-row-buildingsBonus">
      <td class="row-header">\${buildingsBonus}</td><td></td>
    </tr>
    <tr id="scores-row-gold">
      <td class="row-header">\${gold}</td><td><GOLD></td>
    </tr>
    <tr id="scores-row-beggings">
      <td class="row-header">\${beggings}</td><td><BEGGING></td>
    </tr>

    <tr id="scores-row-total">
      <td class="row-header">\${total}</td><td><SCORE></td>
    </tr>
  </tbody>
</table>
`;


</script>

{OVERALL_GAME_FOOTER}
