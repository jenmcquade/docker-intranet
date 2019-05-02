// Export Constants
export const TOGGLE_MENU_PERSPECTIVE = 'TOGGLE_MENU_PERSPECTIVE';
export const TOGGLE_MENU_THEME = 'TOGGLE_MENU_THEME';
export const FLATTEN_OBJECT = 'FLATTEN_OBJECT';
export const RESTORE_OBJECT = 'RESTORE_OBJECT';
export const ZOOM_OUT = 'ZOOM_OUT';
export const ZOOM_IN = 'ZOOM_IN';
export const RESET_MENU_STATE = 'RESET_MENU_STATE';
export const SET_MOBILE_THEME = 'SET_MENU_MOBILE_THEME';
export const SET_DESKTOP_THEME = 'SET_MENU_DESKTOP_THEME';
export const TOGGLE_SETUP = 'MENU_SETUP';

// Export Actions
export function resetMenuState(menu) {
  return {
    type: RESET_MENU_STATE
  };
}

export function toggleMenu(id, forceOn=false, forceOff=false) {
  return {
    type: 'TOGGLE_MENU_' + id.toUpperCase(),
    forceOn: forceOn,
    forceOff: forceOff,
  };
}

export function toggleMenuSetup() {
  return {
    type: TOGGLE_SETUP,
  };
}

export function setMobileTheme() {
  return {
    type: SET_MOBILE_THEME,
  }
}

export function setDesktopTheme() {
  return {
    type: SET_DESKTOP_THEME,
  }
}
