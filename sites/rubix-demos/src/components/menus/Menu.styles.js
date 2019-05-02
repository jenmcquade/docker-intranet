import styled, { css, keyframes } from 'styled-components';
import { Link } from 'react-router-dom'
import animations from '../../animations/menu';
import { Scrollbars } from 'react-custom-scrollbars';
import { 
  Button, 
  ButtonGroup,
  DropdownButton,
  MenuItem, 
  Form,
  FormGroup,
  FormControl,
  InputGroup,
} from 'react-bootstrap';


const anims = new animations(keyframes);

class Styles {
  constructor() {

    //
    // Root 
    //
    this.root = styled.div`
      min-height: 100%;
      width: 100%;
    `;

    //
    // Container
    //
    this.menu = styled.div`
      font-size: 2em;
      justify-content: center;
      box-sizing: border-box;
      z-index: 99;
      display: flex;
      position: absolute;
      background-color: white;

      @media only screen
      and (min-width : 75px) 
      and (max-width : 719px) {
        height: 10rem;
        min-width: 100%;
        z-index: 99;
        padding: 0;
        max-height: 100px;
      }

      @media only screen 
      and (min-width: 720px) 
      and (max-width: 1023px) { 
        z-index: 99;
        display: grid;
      }
    `;

    //
    // Frame of a Menu category
    //

    this.item = styled.div`
      text-align: center;
      transform-origin: top left;
      z-index: default;
      transition: background-color 0.8s forwards;
      height: 2.6em;
      display: inline-grid;
      width: min-content;

      @media only screen
      and (min-width : 75px) 
      and (max-width : 719px) {
        display: grid;
        flex-flow: row wrap;
        cursor: pointer;
        cursor: hand;
        text-align: center;
        transform-origin: top left;
        padding-top: 0px;
        margin: 0 1em 0 1em;
      }

      @media only screen 
      and (min-width: 720px) 
      and (max-width: 1023px) { 
        width: 5.5em;
      }
    `;

    //
    // Buttons to open menu items
    //

    this.trigger = styled(Link)`
      cursor: pointer;
      color: rgba(255,255,255,1);
      text-decoration: none;
      transform-origin: top left;
      transition: color, 0.5s ease;
      transform-style: preserve-3d;
      transform: default;
      width: 100%;
      height: 100%;
      display: table-caption;
      position: relative;
      padding: 0.5em 0.5em 0.5em 0.5em;
      border: none;

      > :focus {
        text-decoration: none;
        border: 1px solid white;
        outline: 0px;
        outline-offset: 0px;
      }

      > :focus, :hover {
        text-decoration: none;
        color: rgba(255,255,255,0.8) !important;
      }

      ${props => props.active === 'false' && props.default !== 'true' && css`
        animation: ${anims.menuTitleFlipUp} 1s forwards ease-out;
      `}

      ${props => props.active === 'true' && props.default !== 'true' && css`
        animation: ${anims.menuTitleDrop}  1s forwards ease-out;
      `}

      @media only screen
      and (min-width : 75px) 
      and (max-width : 719px) {
        animation: "";
        transform: rotateZ(0deg) translateY(0em);
        padding: 0.5em 0.1em 0 0.1em;
        font-size: 1em;
        text-align: center;
        display: table;

        > :focus, :hover {
          text-decoration: none;
          color: rgba(0,0,0,0.2) !important;
        }
      }

      @media only screen 
      and (min-width: 720px) 
      and (max-width: 1023px) { 
        width: 5em;
        padding: 0.5em 1em 0.5em .5em;

      }

      @media only screen 
      and (min-width: 1920px) { 
        max-height: 2.5em;
      }
    `;

    //
    // Trigger text
    //

    this.category = styled.span`
      font-size: 20pt;
      display: inline-grid;
      @media only screen
      and (min-width : 75px) 
      and (max-width : 719px) {
        display:none;
      }
      @media only screen 
      and (min-width: 720px) 
      and (max-width: 1023px) { 
        display:none;
      }
      @media only screen
      and (min-width: 1024px) {
        margin-left: .5em;
      };
    `;

    //
    // Category value, but inline to the content
    //

    this.title = styled.div`
      display: table-column;
      margin: 1em;

      @media only screen 
      and (min-width: 75px) 
      and (max-width: 719px) {
        visibility: visible;
        display: block;
        margin: 0.5em;
      }
      @media only screen 
      and (min-width: 720px) 
      and (max-width: 1023px) {
        display: block; 
        visibility: visible;
        margin: 0.5em;
      }
      @media only screen 
      and (min-width: 1024px) {
        visibility: hidden;
      };
    `;

    //
    // Trigger icon
    //

    this.icon = styled.i`
      font-size: 1em;
      display: inline-grid;
      transform: scale(0.9,0.9);

      @media only screen
      and (min-width : 75px) 
      and (max-width : 719px) {
        margin-top: 0;
        font-size: 2em;
      }
      @media only screen 
      and (min-width: 720px) 
      and (max-width: 1023px) { 
        margin: 0 40%;
      }

      ${props => props.active && css`
        font-size: 2em;
      `}

    `;

    //
    // Content scrollbar
    //

    this.scrollBar = styled(Scrollbars)`

    `

    //
    // Body of the Menu Item
    //

    this.content = styled.div`
      min-width: 15em;
      min-height: 18em;
      margin-top: -3em;
      transform: rotateX(-90deg);
      transition: default;
      padding: 0em;
      z-index: 10;

      ${props => props.backgroundColor && css`
        bgColor = props.backgroundColor;
        background-color: rgba(${props.backgroundColor[0]},${props.backgroundColor[1]},${props.backgroundColor[2]},${props.backgroundColor[3]});
      `}

      @media only screen
      and (min-width : 75px) 
      and (max-width : 719px) {
        position: absolute;
        min-height: 100%;
        min-width: 100%;
        top: 184%;
        left: 0%;
        display: table-row;

        ${props => props.active === 'true' && props.default === 'false' && css`
          transform: rotateX(0deg);
        `}

        ${props => props.scrollable && css`
          :after {
            font-family: "FontAwesome";
            content: "\f078"
          }
        `}

        ${props => props.backgroundColor && css`
          bgColor = props.backgroundColor;
          background-color: rgba(${props.backgroundColor[0]},${props.backgroundColor[1]},${props.backgroundColor[2]},0.7);
        `}
      }

      @media only screen 
      and (min-width: 720px) 
      and (max-width: 1023px) { 
        padding: 0.25em;
        margin-top: -2.42em;
        min-height: 22em;

        ${props => props.active === 'true' && props.default === 'false' && css`
          animation: ${anims.menuOpenContent} 1s forwards ease-out;
        `}

        ${props => props.active === 'false' && props.default === 'false' && css`
          animation: ${anims.menuCloseContent} 1s forwards ease-out;
        `}

      }

      @media only screen 
      and (min-width: 1024px) { 
        padding: 1em 0.25em 1em 0;

        ${props => props.active === 'true' && props.default === 'false' && css`
          animation: ${anims.menuOpenContent} 1s forwards ease-out;
        `}

        ${props => props.active === 'false' && props.default === 'false' && css`
          animation: ${anims.menuCloseContent} 1s forwards ease-out;
        `}

      }
    `;

    //
    // Form headings
    //

    this.h4 = styled.h4`
      margin: 1em;
      font-size: 0.8em;
      font-weight: 400;
      min-width: 3em;
      ${props => props.type && props.type === 'heading' && css`
        margin-top: 0.5em;
      `}
    `

    //
    // Form item lists in the content
    //

    this.ul = styled.ul`
      list-style-type: none;
      margin: auto;
      padding: 0px;
      font-size: 0.7em;
      display: inline-block;
      align-items: flex-end;
    `

    //
    // Form items in the form lists
    //

    this.li = styled.li`
      list-style-type: none;
      margin-bottom: 0.55em;
      display: inline-block;
    `

    //
    // Form label
    //
    this.label = styled.label`
      margin: 0.55em 0.25em 0 0;
      font-weight: 400;
      min-width: 4em;
      display: default;
    `

    //
    // Bootstrap Button design
    //
    this.btnSecondary = styled(Button)`
      cursor: pointer;
      font-size: 1em;
      font-family: 'sans-serif';
    `

    //
    // Styled Components simple design button
    //
    this.btnPrimary = styled.a`
      cursor: pointer;
      display: inline-block;
      border-radius: 3px;
      padding: 0.5rem 0;
      margin: 0rem 1rem;
      width: 11rem;
      background: transparent;
      color: white;
      border: 2px solid white;
      text-transform: uppercase;

      &:hover {
        text-decoration: none;
        color: white;
        background: rgba(255,255,255,0.6);
      }

      ${props => props.primary && css`
        background: white;
        color: black;
      `}
    `

    //
    //  Bootstrap ButtonGroup
    //
    this.btnGroup = styled(ButtonGroup)`

    `;

    //
    //  Bootstrap Form
    //
    this.form = Form;

    //
    //  Bootstrap FormGroup
    //
    this.formGroup = styled(FormGroup)`
      display: flex !important;
    `;
    
    //
    //  Bootstrap InputGroup
    //
    this.inputGroup = InputGroup;

    //
    // Bootstrap Form Control / Text Box
    //
    this.textBox = FormControl;

    //
    // A Button with menu options
    //
    this.dropdownBtn = styled(DropdownButton)`
      margin: 0 0.25em;
      transition: background 0.8s;
      width: 4.5em;
    `;

    //
    // Dropdown menu items
    //
    this.dropdownItem = styled(MenuItem)`
      display: none;
      ${props => props.display && props.display === 'true' && css`
        display: block;
      `}
    `;

    //
    // A status message for services
    //
    this.status = styled.span`
      color: red;
      padding: 0.25em;
      border: 1px solid white;
      background-color: rgba(255,255,255,0.8);
      margin-left: 1em;
      ${props => props.className && props.className === 'true' && css`
        color: green;
      `}
    `

    //
    // IgHistory Component
    //
    this.igHistory = styled.div`
      height: 8em;
      width: 50%;
      margin: auto auto;
      padding: 0.8em;
      display: flex;
      position: absolute;
      left: -3%;
      right: 0%;
      bottom: 0em;
      transform: translateY(8em);
      transition: transform 0.8s;

      ${props => props.active && css`
        transform: translateY(-3em);
      `}

      @media only screen
      and (min-width : 75px) 
      and (max-width : 719px) {
        left: 3%;
        margin-left: 0em;
        width: 80%;
        ${props => props.active && css`
          transform: translateY(-2em);
        `}
      }

      @media only screen 
      and (min-width: 720px) 
      and (max-width: 1023px) { 
        left: 0%;
        width: 65%;
      }
    `

    this.igHistorySide = styled.div`
      display: flex;
      min-width: 2em;
      background-color: rgba(255,255,255,0.8);
      
      ${props => props.id === 'wrap-left' && css`
        border-top-left-radius: 1em;
        border-bottom-left-radius: 1em;
      `}

      ${props => props.id === 'wrap-right' && css`
        border-top-right-radius: 1em;
        border-bottom-right-radius: 1em;
      `}
    `

    this.igHistoryContent = styled.div`
      display: flex;
      min-width: 100%;
      align-items: center;
      background-color: rgba(255,255,255,0.8);
    `

    this.pagingWrapper = styled.div`
      cursor: pointer;
      cursor: hand;
      width: 100%;
      display: flex;
      margin: auto auto;
      padding: 0 0.25em;
      position: absolute;
      transform: translate(50%, -5em);
      transition: transform 0.8s linear;
      align-items: center;

      @media only screen
      and (min-width : 75px) 
      and (max-width : 719px) {
        bottom: 2em;
      }
      
      > i {
        margin: 2%;

        ${props => props.active && css`
          transform: scale(2, 2);
        `}

        @media only screen
          and (min-width : 75px) 
          and (max-width : 719px) {
            margin: 2%;
          }
        }
      }
    `
  }
}

export default Styles;
