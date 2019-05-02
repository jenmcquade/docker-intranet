import { PROXY_DOMAIN, DEFAULT_TYPE, DEFAULT_VALUE } from './env.js'

// Export Constants
export const SET_IS_MOUNTED = 'INSTAPROXY_IS_MOUNTED';
export const SETUP = 'INSTAPROXY_SETUP';
export const SET_IS_OFFLINE = 'INSTAPROXY_IS_OFFLINE';
export const SET_IS_ONLINE = 'SET_IS_ONLINE';
export const SET_ERROR = 'INSTAPROXY_ERROR';
export const SET_SEARCH_VALUE = 'SET_SEARCH_VALUE';
export const SET_IG_SEARCH_TYPE = 'SET_IG_SEARCH_TYPE';
export const SET_IG_SEARCH_VALUE = 'SET_IG_SEARCH_VALUE';
export const SET_IG_SEARCH_URL = 'SET_IG_SEARCH_URL';
export const SET_SEARCH_TYPE = 'SET_SEARCH_TYPE';
export const UPDATE_IG_DATA = 'UPDATE_IG_DATA';
export const TOGGLE_HISTORY_PANEL = 'TOGGLE_HISTORY_PANEL';
export const SEARCH_RETURN_COUNT = 54;
export const SEARCH_DEFAULT_TYPE = DEFAULT_TYPE ? DEFAULT_TYPE : 'hashtag';
export const SEARCH_DEFAULT_VALUE = DEFAULT_VALUE ? DEFAULT_VALUE : 'catsofig';
export const SEARCH_DEFAULT_HASHTAG = 'catsofig';
export const DURATION_SEARCH_DISPATCH = 1500;
export const NULL_REQUEST = 'NULL_REQUEST';
export const PROD_PROXY_URL = 'https://' + PROXY_DOMAIN;
export const PROXY_SERVER = PROD_PROXY_URL;
export const PATH_HASHTAG = '/';
export const URL_BASE_HASHTAG = PROXY_SERVER + PATH_HASHTAG;
export const URL_DEFAULT_SEARCH_URL = PROXY_SERVER + PATH_HASHTAG + '?tag=' + SEARCH_DEFAULT_VALUE;

export function setIsMounted() {
    return {
        type: SET_IS_MOUNTED,
    }
}

export function setup() {
    return {
        type: SETUP,
    }
}

export function setStatus(status) {
    if (status) {
        return {
            type: SET_IS_ONLINE,
        }
    } else {
        return {
            type: SET_IS_OFFLINE,
        }
    }
}

export function setSearchUrl(url) {
    return {
        type: SET_IG_SEARCH_URL,
        value: url,
    }
}

export function setSearchType(searchType) {
    return {
        type: SET_IG_SEARCH_TYPE,
    }
}

export function setSearchValue(searchValue) {
    return {
        type: SET_IG_SEARCH_VALUE,
        value: searchValue,
    }
}

export function updateData(data) {
    return {
        type: UPDATE_IG_DATA,
        value: { data: data },
    }
}

export function setServerError(data) {
    return {
        type: SET_ERROR,
        error: data,
    }
}

export function toggleHistoryPanel(forceOn = false, forceOff = false) {
    return {
        type: TOGGLE_HISTORY_PANEL,
        value: {
            forceOn: forceOn,
            forceOff: forceOff,
        }
    }
}