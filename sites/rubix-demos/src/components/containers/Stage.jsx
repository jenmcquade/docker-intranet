import React from 'react';
import Cube from '../3d/rubix/Cube';
import CubeMenu from '../3d/rubix/CubeMenu';

import Styles from './container.styles';

// Create container styles
const styles = new Styles();
const Wrapper = styles.wrapper;

export default class Stage extends React.Component {
  constructor(props) {
    super(props);
    this.state={
      ...props,
      infoPanelIsOpen: props.appStore.qs.hasOwnProperty('__info'),
    };
    this.handleStart = handleStart.bind(this);
    this.handleDrag = handleDrag.bind(this);
    this.handleStop = handleStop.bind(this);
    this.resetMenus = resetMenus.bind(this);
  }

  render() {
    let qs = this.props.appStore.qs;
    let igStatus = this.props.igStatus;
    let screenSize = this.props.screenSize;
    return(
      <Wrapper id="stage" className="stage" role="main">
        <Cube history={this.props.history} screenSize={screenSize} igStatus={igStatus} hasImagesOnLoad={qs.hasOwnProperty('offline') || !igStatus ? false : true} />
        <CubeMenu/>
      </Wrapper>
    );
  }
}

function resetMenus() {

}

function handleStart() {

}

function handleDrag() {

}

function handleStop() {

}
