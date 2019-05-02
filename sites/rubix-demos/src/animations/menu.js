export default class Menu {
  constructor(keyframes) {
    this.menuReset = keyframes`
      0% {
        transform-origin: bottom right; 
        transform: rotateZ(90deg) translateY(-1330%);
      }
      100% {
        transform: rotateZ(0deg) translateY(0); 
      }
    `;

    this.menuTitleDrop = keyframes`
      0% { 
        z-index:9;
        transform: rotateZ(0deg) translateY(0em);
      }
      25% {
        z-index:9;
        transform: rotateZ(90deg) translateY(0em);
      }
      75%{
        z-index:99;
      }
      100% {
        z-index:99;
        transform: rotateZ(90deg) translateY(-715%);
      }
    `;

    this.menuTitleFlipUp = keyframes`
      0% { 
        z-index:9;
        transform: rotateZ(90deg) translateY(-715%);
      }

      50% {
        z-index:9;
        transform: rotateZ(90deg) translateY(-100%);
      }
      
      100% {
        z-index:9;
        transform: rotateZ(0deg) translateY(0em);
      }
    `;

    this.menuOpenContent = keyframes`
      0% {
        z-index:9;
        transform: translateX(-150%);  
      }
      25% {
        transform: translateX(-100%);  
      }
      50% {
        transform: translateX(-50%);
      }
      100% {
        z-index:98;
        transform: translateX(0%);
      }		
    `;

    this.menuCloseContent = keyframes`
      0% {
        z-index:98;
        transform: translateX(0%);
      }

      25% {
        transform: translateX(-50%);
      }

      50% {
        transform: translateX(-100%);
      }
      
      100% {
        z-index:9;
        transform: translateX(-200%); 
      }
    `;
  }
}
