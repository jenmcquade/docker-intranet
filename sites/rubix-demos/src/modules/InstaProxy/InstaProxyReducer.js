// Import Actions
import {
    SET_IS_MOUNTED,
    SETUP,
    UPDATE_IG_DATA,
    SET_IS_ONLINE,
    SET_IS_OFFLINE,
    SET_ERROR,
    SET_IG_SEARCH_TYPE,
    SET_IG_SEARCH_VALUE,
    NULL_REQUEST,
    SEARCH_RETURN_COUNT,
    PROXY_SERVER,
    PATH_HASHTAG,
    SET_IG_SEARCH_URL,
    URL_DEFAULT_SEARCH_URL,
    TOGGLE_HISTORY_PANEL,
} from './InstaProxyActions';

const payloadHistoryEmptySet = []

// Initial State
const initialState = {
    isMounted: false,
    searchType: 'hashTag',
    searchValue: 'catsofig',
    urlBaseHashtag: PROXY_SERVER + PATH_HASHTAG,
    url: URL_DEFAULT_SEARCH_URL,
    lastPayload: {},
    error: {},
    status: false,
    returnCount: SEARCH_RETURN_COUNT,
    inProcess: false,
    payloadHistory: payloadHistoryEmptySet,
    historyPanelIsOpen: false,
};

const InstaProxyReducer = (state = initialState, action) => {
    let newState = Object.assign({}, state);
    switch (action.type) {
        case SET_IS_MOUNTED:
            newState.isMounted = true;
            return {...state, ...newState };

        case SETUP:
            newState.inProcess = true;
            return { state, ...newState };

        case SET_IS_ONLINE:
            newState.status = true;
            return {...state, ...newState };

        case SET_IS_OFFLINE:
            newState.status = false;
            return {...state, ...newState };

        case SET_ERROR:
            newState.error = state.error;
            return {...state, ...newState };

        case SET_IG_SEARCH_URL:
            newState.url = action.value;
            return {...state, ...newState };

        case SET_IG_SEARCH_VALUE:
            newState.searchValue = action.value;
            return {...state, ...newState };

        case SET_IG_SEARCH_TYPE:
            newState.searchType = action.value;
            return {...state, ...newState };

        case UPDATE_IG_DATA:
            if (!action.value.data || state.inProcess) {
                return {...state };
            }
            newState.inProcess = false;
            newState.lastPayload = action.value.data;
            newState.payloadHistory.push(action.value.data.graphql.hashtag.edge_hashtag_to_media.edges)
            return {...state, ...newState };

        case TOGGLE_HISTORY_PANEL:
            newState.historyPanelIsOpen = !newState.historyPanelIsOpen;
            if (action.value.forceOff) {
                newState.historyPanelIsOpen = false;
            }
            if (action.value.forceOn) {
                newState.historyPanelIsOpen = true;
            }
            return {...newState, state };

        case NULL_REQUEST:
            return {...state }

        default:
            return state;
    }
};

/* Selectors */

// Getters


// Export Reducer
export default InstaProxyReducer;