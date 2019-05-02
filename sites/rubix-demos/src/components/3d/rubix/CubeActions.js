// Export Constants
export const RESTORE_OBJECT = 'RESTORE_OBJECT';
export const FLATTEN_OBJECT = 'FLATTEN_OBJECT';
export const ZOOM_OUT = 'ZOOM_OUT';
export const ZOOM_IN = 'ZOOM_IN';
export const SET_FACE_RGBA = 'SET_FACE_RGBA';
export const SET_FACE_TXT = 'SET_FACE_TXT';
export const RESET_FACE_RGBA = 'RESET_FACE_RGBA';
export const RESET_FACE_TXT = 'RESET_FACE_TXT';
export const SET_THEME_FACE_IMAGES = 'SET_THEME_FACE_IMAGES';
export const SET_THEME_CUBE_IMAGES = 'SET_THEME_CUBE_IMAGES';
export const SET_IMAGES_TO_LOADING = 'SET_IMAGES_TO_LOADING';
export const SET_THEME_FACE_IMAGE_OPACITY = 'SET_FACE_IMAGE_OPACITY';
export const SET_THEME_CUBE_IMAGE_OPACITY = 'SET_ALL_IMAGE_OPACITY';

export function restoreObject() {
  return {
    type: RESTORE_OBJECT,
  }
}

export function flattenObject() {
  return {
    type: FLATTEN_OBJECT,
  }
}

export function zoomOut() {
  return {
    type: ZOOM_OUT,
  }
}

export function zoomIn() {
  return {
    type: ZOOM_IN,
  }
}

export function setThemeRGBA(face) {
  return {
    type: SET_FACE_RGBA,
    value: {face: face.id, bgColor: face.bgColor},
  }
}

export function setThemeTxtColor(face) {
  return {
    type: SET_FACE_TXT,
    value: {face: face.id, txtColor: face.txtColor},
  }
}

export function setThemeFaceImages(images) {
  return {
    type: SET_THEME_FACE_IMAGES,
    value: images,
  }
}

export function setThemeCubeImages(images) {
  return {
    type: SET_THEME_CUBE_IMAGES,
    value: images,
  }
}

export function resetThemeRGBA(faceId) {
  return {
    type: RESET_FACE_RGBA,
    value: {face: faceId},
  }
}

export function resetThemeTxt(faceId) {
  return {
    type: RESET_FACE_TXT,
    value: {face: faceId},
  }
}

export function resetThemeImages(faceId) {
  return {
    type: SET_IMAGES_TO_LOADING,
    value: faceId
  }
}

export function setThemeCubeImageOpacity(faces, opacity) {
  return {
    type: SET_THEME_CUBE_IMAGE_OPACITY,
    value: {faces: faces, opacity: opacity},
  }
}

export function setThemeFaceImageOpacity(faceId, opacity) {
  return {
    type: SET_THEME_FACE_IMAGE_OPACITY,
    value: {faceId: faceId, opacity: opacity},
  }
}

