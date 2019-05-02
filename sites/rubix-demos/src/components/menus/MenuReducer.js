// Import Actions
import { 
  TOGGLE_MENU_PERSPECTIVE, 
  TOGGLE_MENU_THEME,
  SET_MOBILE_THEME,
  SET_DESKTOP_THEME,
  RESET_MENU_STATE,
  TOGGLE_SETUP,
} from './MenuActions';

// Initial State
const initialState = {
  isDefaultState: true,
  isInSetup: true,
  categories: {
    perspective: {
      triggerColor: 'white',
      baseColor: [255,0,0,1],
      backgroundColor: 'rgba(255,0,0,1)',
      menuIsOpen: false,
      isDefaultState: true,
      inlineContentTransform: {},
    },
    theme: {
      triggerColor: 'white',
      baseColor: [0,0,255,1],
      backgroundColor: 'rgba(0,0,255,1)',
      menuIsOpen: false,
      isDefaultState: true,
      inlineContentTransform: {},
    },
  },
};

function getThemeRGBA(themeColorArray) {
  let prop = 'rgba(';
  for(var c in themeColorArray) {
    prop += themeColorArray[c] + ','
  }
  let trimmedProp = prop.slice(0, -1); //remove last comma
  trimmedProp += ')';
  return trimmedProp;
}

const MenuReducer = (state = initialState, action) => {
  let newState = Object.assign({}, state);
  var menu;

  switch (action.type) {

    case RESET_MENU_STATE:
      for(menu in newState.categories) {
        newState.categories[menu].isDefaultState = true;
      }
      newState.isDefaultState = true;
      return {...newState, state};

    case TOGGLE_SETUP:
      newState.isInSetup = !newState.isInSetup;
      return {...newState, ...state};

    case TOGGLE_MENU_PERSPECTIVE:
      newState.categories['perspective'].isDefaultState = false;
      newState.isDefaultState = false;
      for(menu in newState.categories) {
        if (menu === 'perspective') {
          continue;
        }
        newState.categories[menu].menuIsOpen = false;
      }
      newState.categories['perspective'].menuIsOpen = !newState.categories['perspective'].menuIsOpen;
      if(action.forceOn) {
        newState.categories['perspective'].menuIsOpen = true;
      }
      if(action.forceOff) {
        newState.categories['perspective'].menuIsOpen = false;
      }
      return {...newState, state};

    case TOGGLE_MENU_THEME:
      newState.categories['theme'].isDefaultState = false;
      newState.isDefaultState = false;
      for(menu in newState.categories) {
        if (menu === 'theme') {
          continue;
        }
        newState.categories[menu].menuIsOpen = false;
      }
      newState.categories['theme'].menuIsOpen = !newState.categories['theme'].menuIsOpen;
      if(action.forceOn) {
        newState.categories['theme'].menuIsOpen = true;
      }
      if(action.forceOff) {
        newState.categories['theme'].menuIsOpen = false;
      }
      return {...newState, state};

    case SET_MOBILE_THEME:
      for(menu in newState.categories) {
        newState.categories[menu].backgroundColor = 'white';
        newState.categories[menu].triggerColor = getThemeRGBA(newState.categories[menu].baseColor);
      }
      return newState;

    case SET_DESKTOP_THEME:
      for(menu in newState.categories) {
        newState.categories[menu].backgroundColor = getThemeRGBA(newState.categories[menu].baseColor);
        newState.categories[menu].triggerColor = 'white';
      }
      return {...newState, state};

    default:
      return state;
  }
};

/* Selectors */

// Get isMenuOpen
export const getIsPerspectiveOpen = state => state.categories.perspective.menuIsOpen;
export const getIsThemeOpen = state => state.categories.theme.menuIsOpen;

// Export Reducer
export default MenuReducer;
