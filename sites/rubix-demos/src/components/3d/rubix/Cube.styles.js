import styled, { keyframes, css } from 'styled-components';
import Rotations from '../../../animations/rotations';

const rots = new Rotations(keyframes);

class Styles {
  constructor() {
    this.root = styled.div`
      min-height: 100%;
      width: 100%;
    `;

    this.handle = styled.div`
      top: 50%;
      left: 50%;
      position: absolute;    
    `

    this.wrapper = styled.div`
      top: 50%;
      left: 50%;
      margin-left: 2em;
      position: absolute;
      z-index: 5;
      transform: scale(0.4); 
      transition: transform 0.5s ease-in-out;
      @media only screen 
      and (min-width : 75px) 
      and (max-width : 667px) 
      { 
        margin-left: -8em;
        transform: scale(0.6);
      }
      @media only screen 
      and (min-width: 668px)
      and (max-width: 719px){ 
        margin-left: -10.5em;
        transform: scale(0.8);
      }
      @media only screen 
      and (min-width: 720px) 
      and (max-width: 1023px) { 
        margin-top: -5%;
        margin-left: -1%;
        transform: scale(0.8);
      }
      @media only screen 
      and (min-width: 1024px) { 
        margin-top: 0%;
        margin-left: 0%;
        transform: scale(1);
      }
    `;

    this.cube = styled.div`
      border: 5px solid white;
      box-sizing: border-box;
      position: inherit;
      display: grid;
      transform-origin: top left;
      grid-template-columns: auto auto auto;
      grid-template-rows: auto auto auto;
      transform-style: preserve-3d;
      text-align: center;
      font-size: 2.25em;
      transform: scale(1,1);
      @media only screen 
      and (min-width : 75px) 
      and (max-width : 667px) 
      { 
        animation: ${rots.simpleRotateX} 10s infinite ease-in-out;
      }
      @media only screen 
      and (min-width: 668px)
      and (max-width: 719px){ 
        animation: ${rots.simpleRotateX} 20s infinite ease-in-out;
      }
      @media only screen 
      and (min-width: 720px) 
      and (max-width: 1023px) { 
        animation: ${rots.simpleRotateX} 20s infinite ease-in-out;
      }
      @media only screen 
      and (min-width: 1024px) { 
        animation: ${rots.spin} 60s infinite ease-in-out;
      }
      
      ${props => props.flat && css`
        > div {
          background-color: transparent;
          box-shadow: white 0px 0px 0px 0px inset;
          border-radius: 1%;
        }
      `}
    `;

    this.face = styled.div`
      user-drag: none;
      min-width: 200%;
      min-height: 200%;
      display: grid;
      grid-template-columns: auto auto auto;
      grid-template-rows: auto auto auto;
      grid-row-start: span 3;
      grid-gap: auto auto;
      grid-auto-flow: row;
      justify-items: stretch;
      align-items: stretch;
      align-content: stretch;
      place-content: stretch;
      box-sizing: border-box;
      position: absolute;
      justify-content: center;
      transform-style: preserve-3d;
      transform-origin: top left;
      padding: auto auto;
      box-shadow: inset 0px 0px 50px 0px white;
      border-radius: 10%;
      background-color: rgba(0,0,0,0.7);
      transition: transform 0.8s;

      ${props => props.itemBgColor && css`
        > div {
          background-color: ${props.itemBgColor};
	        color: ${props.itemColor ? props.itemColor : 'white'};
        }
      `}

      ${props => props.id && props.id === 'top' && css`
        z-index: 1;
      `}

    `;

    this.item = styled.div`
      user-drag: none;
      align-items: center;
      justify-content: center;
      width: 100px;
      height: 100px;
      display: flex;
      border-radius: 25%;
      margin: 10px;
      box-shadow: inset 0 0 0.7em #fff;
      transform-style: preserve-3d;
      position: relative;
      text-transform: uppercase;
      font-size: 0.8em;
      opacity: 0.99;
      transition: background 0.8s linear, transform 1s ease-in-out;
    `;

    this.itemImage = styled.div`
      align-items: center;
      border-radius: 0%;
      justify-content: center;
      border-radius: 25%;
      width: 100px;
      height: 100px;
      display: flex;
      text-transform: uppercase;
      font-size: 0.8em;
      opacity: 0.5;

      >label {
        display: none;
      }
    `

  }
}

export default Styles;
