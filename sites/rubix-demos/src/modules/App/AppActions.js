// Export Constants
export const RESIZE = 'RESIZE';
export const SET_IS_MOUNTED = 'APP_IS_MOUNTED';
export const SET_QS = 'SET_QUERYSTRING';
export const TOGGLE_INFO_PANEL = 'TOGGLE_INFO_PANEL';

// Export Actions
export function resize() {
  return {
    type: RESIZE,
  };
}

export function setIsMounted() {
  return {
    type: SET_IS_MOUNTED,
  }
}

export function setQs() {
  return {
    type: SET_QS,
  }
}

export function toggleInfoPanel(forceOn = false, forceOff = false) {
  return {
    type: TOGGLE_INFO_PANEL,
    value: {
      forceOn: forceOn,
      forceOff: forceOff,
    }
  }
}