import React from 'react';
import {
  SubTitle
} from './menus/Common';

export default class EnvInfo extends React.Component {
  render() {
    return(
      <div id="buildInfo" className="build-info">
        <SubTitle>
          Environment
        </SubTitle>
        {
          Object.keys(process.env).map((type, i) => {
            return <div key={type}><label>{type}:</label><span> {process.env[type]}</span></div>
          })
        }
      </div>
    );
  }
}

