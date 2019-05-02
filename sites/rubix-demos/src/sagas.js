import { put, takeEvery } from 'redux-saga/effects'

function* returnNull(action) {
  yield put({type: 'APP_SAGA_NULL_ACTION'});
  return null;
}

/*
  Starts fetchUser on each dispatched `USER_FETCH_REQUESTED` action.
  Allows concurrent fetches of user.
*/
function* rootSaga() {
  yield takeEvery('APP_ACTION', returnNull);
}

export default rootSaga;