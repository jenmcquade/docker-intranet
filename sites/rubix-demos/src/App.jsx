/**
 * CONSUMED BY INDEX.JS
 * The primary goal of this file is as an entry point into the src/modules/App module
 * It also sets up the store and router history and passes them down
 */

import React from 'react';
import PropTypes from 'prop-types';
import { Provider } from 'react-redux';
import { ConnectedRouter } from 'react-router-redux'
import AppWrap from './modules/App/App';
import loader from './assets/loader.gif';
import registerServiceWorker from './registerServiceWorker';
import createHistory from 'history/createBrowserHistory';

const history = createHistory();

export default function App(props) {
  return (
    <Provider store={props.store}>
      <ConnectedRouter history={history}>
        <AppWrap router={history}>
          <div id="loadingSpinner">
            <img alt="The app is loading..." style={{width: '200px'}} src={loader} />
          </div>
        </AppWrap>
      </ConnectedRouter>
    </Provider>
  );
};

App.propTypes = {
  store: PropTypes.object.isRequired,
};

registerServiceWorker();

