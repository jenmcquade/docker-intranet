/**
 * Root Reducer
 */
import { combineReducers } from 'redux';

// Import Reducers
import { routerReducer } from 'react-router-redux';
import app from './modules/App/AppReducer';
import instaProxy from './modules/InstaProxy/InstaProxyReducer';
import menu from './components/menus/MenuReducer';
import rubix from './components/3d/rubix/CubeReducer';

// Combine all reducers into one root reducer
export default combineReducers({
  routerReducer,
  app,
  instaProxy,
  menu,
  rubix,
});