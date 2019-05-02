import styled, { css } from 'styled-components';
import { Link } from 'react-router-dom'

class Styles {
    constructor() {

            this.wrapper = styled.div `
      min-height: 100%;
      min-width: 100%;
      position: absolute;
    `;

            this.info = styled(Link)
            `
      position: relative;
      bottom: 1em;
      z-index: 0;
      @media only screen 
      and (min-width : 75px) 
      and (max-width : 667px) 
      { 
        font-size: 0.5em;
      }
    `

            this.infoWrap = styled.div `
      min-width: 15em; 
      font-size: 1em; 
      position: absolute; 
      bottom: 0px; 
      padding: 1em; 
      letterSpacing: 0.10em;
      transition: transform 0.8s;
      transform: translateY(0em);

      > div:not(:nth-child(2)) {
        padding: 0.25em;
        background: rgba(0,0,0,0.5);
      }

      ${props => props.isOpen && css`
        transform: translateY(0em);
      `}

      ${props => !props.isOpen && css`
        transform: translateY(${process.env.NODE_ENV === 'development' ? '17em' : '10em'});
      `}
    `

    this.hrule = styled.div`
      border-bottom: 1px solid white;
    `

    this.gitHubLink = styled.a`
      right: 0em;
      top: 0em;
      position: absolute;
      float: right;
      z-index: 99;
    `

  }
}
export default Styles;