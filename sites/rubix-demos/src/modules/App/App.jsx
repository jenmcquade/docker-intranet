/**
 * This is the primary App framework for O3DV
 * --------------------------------------------------------------
 * # It takes the Redux store attributes and 
 *   passes them along as props down
 *   to its children.
 * # This ensures that fewer calls are made 
 *    directly to the store when accessing its 
 *    properties, which speeds up rendering
 * # Some components take advantage of the
 *   lifecycle event componentWillReceiveProps
 *   in order to trigger actions, dispatch to the store
 *   and modify state prior to rendering.
 * # componentWillReceiveProps may mask some
 *   performance flaws, due to multiple state changes
 *   down to all children, so use it sparingly when developing.
 */

import React, { Component } from 'react';
import { push } from 'react-router-redux'
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import { Link } from 'react-router-dom'
import Styles from '../../components/containers/container.styles';

// Additional Modules
import InstaProxy from '../InstaProxy/InstaProxy';

// Import Components
import Helmet from 'react-helmet';
import DevTools from '../../components/DevTools';
import Stage from '../../components/containers/Stage';
import Menu from '../../components/containers/Menu';
import IgHistory from '../../components/IgHistory';
import EnvInfo from '../../components/EnvInfo';
import ProxyInfo from '../../components/ProxyInfo';

// Import Actions
import {
  setIsMounted,
  resize,
  setQs,
  toggleInfoPanel,
} from './AppActions'
import { 
  toggleMenu, 
} from '../../components/menus/MenuActions';
import { 
  toggleHistoryPanel, 
} from '../InstaProxy/InstaProxyActions';

let styles = new Styles();
const Wrapper = styles.wrapper;
const InfoLink = styles.info;
const InfoWrap = styles.infoWrap;
const HRule = styles.hrule;
const GitHubLink = styles.gitHubLink;

// CONSTANTS
const DURATION_RESIZE_DISPATCH = 200;

export class App extends Component {
  constructor(props) {
    super(props);
    this.state = {
      ...props,
      igProxyIsOnline: false,
      igHistoryIsOpen: false,
    }
    this.fetchOnLoad = this.props.app.qs.hasOwnProperty('offline') ? false : true;
    this.shouldUpdateStoreWithNewDims = true;
    this.updateQs = updateQs.bind(this);
    this.resetMenus = resetMenus.bind(this);
    this.toggleAppInfoPanel = toggleAppInfoPanel.bind(this);
  }

  /**
   * Calculate & Update state of new dimensions
   */
  updateDimensions() {
    if(!this.shouldUpdateStoreWithNewDims) {
      return false;
    }
    this.shouldUpdateStoreWithNewDims = false;
    setTimeout( () => {
        this.props.dispatch(resize());
        this.shouldUpdateStoreWithNewDims = true;
    }, DURATION_RESIZE_DISPATCH);
  }

  componentDidMount() {
    window.addEventListener('resize', this.updateDimensions.bind(this));
    window.addEventListener('hashchange', this.updateQs.bind(this));
    this.setState({isMounted: true}); // For immediate state checking
    this.props.dispatch(setIsMounted()); // For state checking in store
  }

  componentWillUnmount() {
    window.removeEventListener('resize', this.updateDimensions.bind(this));
    window.removeEventListener('hashchange', this.updateQs.bind(this));
  }

  render() {
    let queryChar = window.location.search.indexOf('?') === -1 ? 
      window.location.search.split('?')[1] ? '&' : '?' 
      : '?';
    let igStatus = this.props.ig.status;
    return (
      <div>
        {
          // Open React dev tools in development and 
          //  no browser extension installed
          this.state.isMounted && 
          !window.devToolsExtension && 
          process.env.NODE_ENV === 'development' && 
          <DevTools />
        }
        <div style={{display: 'flex'}}>
          { 
            // https://github.com/nfl/react-helmet 
          }
          <Helmet
            title="Open 3D Viewer"
            titleTemplate="%s - Default App"
            meta={[
              { charset: 'utf-8' },
              {
                'http-equiv': 'X-UA-Compatible',
                content: 'IE=edge',
              },
              {
                name: 'viewport',
                content: 'width=device-width, initial-scale=1',
              },
            ]}
          />

          <Menu 
            router={this.props.router} 
            screenSize={this.props.app.screenSize} 
          />

          <GitHubLink href="https://github.com/jonmcquade/rubix-demos">
            <img style={{position: 'absolute', top: 0, right: 0, border: 0}} src="https://camo.githubusercontent.com/52760788cde945287fbb584134c4cbc2bc36f904/68747470733a2f2f73332e616d617a6f6e6177732e636f6d2f6769746875622f726962626f6e732f666f726b6d655f72696768745f77686974655f6666666666662e706e67" alt="Fork me on GitHub" data-canonical-src="https://s3.amazonaws.com/github/ribbons/forkme_right_white_ffffff.png" />
          </GitHubLink>
          
          <Wrapper onDoubleClick={this.resetMenus}>
            <Stage 
              router={this.props.router}
              screenSize={this.props.app.screenSize} 
              appStore={this.props.app} 
              igStatus={this.props.ig.status} 
              ig={this.props.ig}
              imageStyle={this.props.object.theme}
            />
          </Wrapper>

          <InfoWrap isOpen={ this.props.app.infoPanelIsOpen } ref="InfoWrapper" id="infoWrapper">
            <InfoLink 
              to={        
                window.location.pathname + queryChar + 
                (window.location.search.indexOf('__info') === -1 ? '__info' : '') +
                window.location.hash 
              }
              onClick={this.toggleAppInfoPanel}
            >
              <i className='fa fa-info-circle'/>
            </InfoLink>
            <HRule />
            { <ProxyInfo igStatus={igStatus} /> }
            { process.env.NODE_ENV !== 'production' && <EnvInfo/> }
            { <ProdBuildInfo /> }
          </InfoWrap>
          
          { process.env.NODE_ENV !== 'production' && 
            <IgHistory 
              ig={this.props.ig}
              history={this.props.history} 
              screenSize={this.props.app.screenSize} 
              igStatus={this.props.ig.status}
              imageStyle={this.props.imageStyle}
            /> 
          }

          <InstaProxy 
            router={this.props.router} 
            fetchOnLoad={this.fetchOnLoad} 
          />
        </div>
      </div>
    );
  }
}
/** 
 * Keep the query string fresh in the store
*/
function updateQs() {
  this.props.dispatch(setQs());
}

/** 
 * This grabs the hidden prodBuildInfo div content and inserts into the app
*/
const ProdBuildInfo = () => {
  let buildInfo = document.querySelector('#prodBuildInfo').innerHTML;
  return <div dangerouslySetInnerHTML={{ __html: buildInfo}}/>
}

/** 
 * Click handler for info panel
*/
function toggleAppInfoPanel() {
  this.props.dispatch(toggleInfoPanel());
}

/** 
 * Double-click action to hide active menus
*/
function resetMenus() {
  if(!this.props.menu.isDefaultState) {
    let categories = Object.keys(this.props.menu.categories);
    for(let category in categories) {
      let categoryId = categories[category];
      if(!this.props.menu.categories[categoryId].isDefaultState) {
        this.props.dispatch(toggleMenu(categoryId, false, true));
      }
    }
  }
  this.props.dispatch(toggleHistoryPanel(false, true));
  this.props.dispatch(toggleInfoPanel(false, true));
  this.props.dispatch(push('/'));
}

App.propTypes = {
  dispatch: PropTypes.func.isRequired,
};

// Retrieve data from store as props
function mapStateToProps(store) {
  return {
    app: store.app,
    menu: store.menu,
    object: store.rubix,
    ig: store.instaProxy,
  };
}

export default connect(mapStateToProps)(App);

