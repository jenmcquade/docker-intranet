import styled from 'styled-components';

class Text {
  constructor(keyframes) {
    this.fade = keyframes`
      0% {
        transition: color ease-in-out;
      }
      100% {
        color: rgba(255,255,255,0.7);
        transition: color .25s ease-in-out;
      }
    `;

    this.fadeToBlack = keyframes`
      0% {
        transition: color ease-in-out;
        -moz-transition: color;
        -webkit-transition: color;
      }
      100% {
        color: rgba(0,0,0,0.7);
        transition: color .25s ease-in-out;
        -moz-transition: background-color .25s ease-in-out;
        -webkit-transition: background-color .25s ease-in-out;
      }
    `;
  }
}

export default Text;
