import React, { Component } from 'react';
import { connect } from 'react-redux';

//
// Import Styled Components and React Bootstrap Components
//
import Common, {
  ScrollBar,
  SubTitle,
  Sub,
  MenuAction,
  Button, 
  ButtonsGroup,
  ButtonInGroup,
  Label, 
} from './Common';

//
// Import Actions
//
import {
  flattenObject,
  restoreObject,
  zoomIn,
  zoomOut
} from '../3d/rubix/CubeActions'

//
// COMPONENT 
//
class Perspective extends Component {
  /**
   * Constructor
   * 
   * 1. Merge new Common object with this
   * 2. Set the menu color theme
   * 3. Set state to Redux store
   * 4. Local properties and bindings
   * 
   * @param {*} props 
   */
  constructor(props) {
    super(props);
    Object.assign(this, new Common(this));
    
    this.state = {
      isDefaultState: true,
      app: props.app,
      menu: props.menu,
      rubix: props.rubix,
    }

    // Binders
    this.flatten = this.flatten.bind(this);
    this.restore = this.restore.bind(this);
    this.scaleIn = this.scaleIn.bind(this);
    this.scaleOut = this.scaleOut.bind(this);
  }

  //
  // Object/Stage handlers
  //
  flatten() {
    this.props.dispatch(flattenObject());
  }

  restore() {
    this.props.dispatch(restoreObject());
  }

  scaleOut() {
    this.props.dispatch(zoomOut());
  }

  scaleIn() {
    this.props.dispatch(zoomIn());
  }

  //
  // Lifecycle handlers
  //
  componentDidUpdate() {
    this.setScreenTheme(this.props);
  }

  //
  // Render to the Menu container
  //
  render() {
    return (
      <ScrollBar
        autoHide 
        autoHideTimeout={1000} 
        autoHideDuration={200} 
        autoHeight 
        autoHeightMin={400} 
        autoHeightMax={550}
      >
        <SubTitle type="heading">
          Transform
        </SubTitle>
        <Sub>
          <MenuAction><Button onClick={this.flatten}>Flatten</Button></MenuAction>
          <MenuAction><Button onClick={this.restore}>Build</Button></MenuAction>
          <MenuAction>
            <Label>Zoom</Label>
            <ButtonsGroup role="group" aria-label="zoom">
              <ButtonInGroup onClick={this.scaleOut} type="button">-</ButtonInGroup>
              <ButtonInGroup onClick={this.scaleIn} type="button">+</ButtonInGroup>
            </ButtonsGroup>
          </MenuAction>
        </Sub>
      </ScrollBar>
    );
  }
}

// Retrieve data from store as props
function mapStateToProps(store) {
  return {
    app: store.app,
    menu: store.menu,
    rubix: store.rubix,
  };
}

export default connect(mapStateToProps)(Perspective);