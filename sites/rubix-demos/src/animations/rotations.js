export default class Rotations {
  constructor(keyframes) {
    this.simpleRotateX = keyframes`
        0% {
          transform: rotateX(0);
        }
        
        50% {
          transform: rotateX(360deg);
        }
        
        100% {
          transform: rotateX(0);
        }
    `;

    this.simpleRotateY = keyframes`
      0% {
        transform: rotateY(0);
      }
      
      50% {
        transform: rotateY(360deg);
      }
      
      100% {
        transform: rotateY(0);
      }
    `;

    this.spin = keyframes`
      0% {
        transform: rotateY(0) rotateX(0);
      }

      50% {
        transform: rotateY(760deg) rotateX(760deg);
      }
      
      100% {
        transform: rotateY(0) rotateX(0);
      }
    `;
  }
}
