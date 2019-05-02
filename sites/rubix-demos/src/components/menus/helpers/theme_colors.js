//
// Import Actions
//
import {
  setThemeRGBA,
  setThemeTxtColor,
  resetThemeRGBA,
  resetThemeTxt,
} from '../../3d/rubix/CubeActions'
import { getCubeFaces } from '../../3d/rubix/Cube'

export function changeBgColor(e){
  e.persist();
  let faceId = e.target.id.split('-')[1];
  if(e.target.value === '') {
    this.props.dispatch(resetThemeRGBA(faceId));
    return true;
  }
  if(!convertStringToThemeRGBA(e.target.value)) {
    return false;
  }
  let value = {
    id: e.target.id.split('-')[1],
    bgColor: convertStringToThemeRGBA(e.target.value),
  }
  if(value.bgColor) {
    this.props.dispatch(setThemeRGBA(value));
  }
}

export function changeAllBgColor(e) {
  e.persist();
  let faces = getCubeFaces();
  let face = 0;
  for(face in faces) {
    e.target.id = 'searchTextBgColor-' + faces[face]
    this.changeBgColor(e);
  }
}

export function changeTxtColor(e) {
  e.persist();
  let faceId = e.target.id.split('-')[1];
  if(e.target.value === '') {
    this.props.dispatch(resetThemeTxt(faceId));
  }
  let value = {
    id: faceId,
    txtColor: e.target.value,
  }
  if(value.txtColor) {
    this.props.dispatch(setThemeTxtColor(value));
  }
}

export function changeAllTxtColor(e) {
  e.persist();
  let faces = getCubeFaces();
  let face = 0;
  for(face in faces) {
    if (face === 'all') {
      continue;
    }
    e.target.id = 'searchTextColor-' + faces[face]
    this.changeTxtColor(e);
  }
}

export function convertStringToThemeRGBA(value) {
  value = value.replace(/ /g,' ');
  let themeRGBA = 'rgba(';
  let colorArray = value.split(',');
  if(colorArray.length === 1 && colorArray[0].length > 2) {
    return colorArray[0];
  }
  if(colorArray.length === 3) {
    colorArray.push(1)
  }
  if(colorArray.length < 4 || colorArray[colorArray.length-1] === '') {
    return false;
  }
  for(var color in colorArray) {
    if(colorArray[colorArray.length-1] === colorArray[color]){
      themeRGBA += colorArray[color]
    }else{
      themeRGBA += colorArray[color] + ','
    }
  }
  themeRGBA += ')';
  return themeRGBA;
}

export function convertRGBAToString(value) {
  let leftPar = value.indexOf('(');
  let rightPar = value.indexOf(')');
  if(leftPar === -1 && rightPar === -1 && value[0].length > 2) {
    return value[0];
  }
  let colorString = value.substring(leftPar+1, rightPar);
  let colorArray = colorString.split(',');
  if(colorArray.length === 3) {
    colorArray.push(1);
  }
  if(colorArray.length < 4 || colorArray[colorArray.length-1] === '') {
    return false;
  }
  for(var color in colorArray) {
    if(colorArray[colorArray.length-1] === colorArray[color]){
      colorArray[color] = parseFloat(colorArray[color]).toFixed(1);
    } else {
      colorArray[color] = parseInt(colorArray[color], 10);
    }
  }
  return colorArray.join(', ');
}