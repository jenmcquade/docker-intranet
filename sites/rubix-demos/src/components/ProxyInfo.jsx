import React from 'react';

import {
  SubTitle, Status,
} from './menus/Common'; 

export default class ProxyInfo extends React.Component {
  render() {
    let proxyIsOnline = this.props.igStatus;
    return(
      <div>
        <SubTitle>Web Services</SubTitle>
        <label>Instagram API Status:</label>
        <Status className={proxyIsOnline.toString()}>
          {proxyIsOnline ? 'Online' : 'Offline'}
        </Status>
      </div>
    );
  }
}


