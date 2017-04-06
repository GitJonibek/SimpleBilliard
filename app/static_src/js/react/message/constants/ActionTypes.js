//TODO: Grouping each feature

export const FETCH_INITIAL_DATA = 'FETCH_INITIAL_DATA'
export const FETCH_MORE_MESSAGES = 'FETCH_MORE_MESSAGES'
export const LOADING = 'LOADING'
export const LOADING_MORE = 'LOADING_MORE'
export const SAVING = 'SAVING'
export const SAVE_SUCCESS = 'SAVE_SUCCESS'
export const SAVE_ERROR = 'SAVE_ERROR'
export const UPLOAD_START = 'UPLOAD_START'
export const UPLOADING = 'UPLOADING'
export const UPLOAD_SUCCESS = 'UPLOAD_SUCCESS'
export const UPLOAD_ERROR = 'UPLOAD_ERROR'
export const DELETE_UPLOADED_FILE = 'DELETE_UPLOADED_FILE'
export const CHANGE_MESSAGE = 'CHANGE_MESSAGE'
export const SET_RESOURCE_ID = 'SET_RESOURCE_ID'
export const SET_TOPIC_ON_DETAIL = 'SET_TOPIC_ON_DETAIL'
export const RESET_DETAIL_STATES = 'RESET_DETAIL_STATES'
export const RESET_SAVE_MESSAGE_STATUS = 'RESET_SAVE_MESSAGE_STATUS'
export const RESET_FETCH_MORE_MESSAGES_STATUS = 'RESET_FETCH_MORE_MESSAGES_STATUS'
export const UPDATE_READ_COUNT = 'UPDATE_READ_COUNT'

// topic title setting
export const CHANGE_TOPIC_TITLE_SETTING_STATUS = 'CHANGE_TOPIC_TITLE_SETTING_STATUS'
export const SAVE_TOPIC_TITLE_SUCCESS = 'SAVE_TOPIC_TITLE_SUCCESS'
export const SAVE_TOPIC_TITLE_ERROR = 'SAVE_TOPIC_TITLE_ERROR'

// topic list page
export const FETCHING = 'FETCHING'
export const INITIALIZE = 'INITIALIZE'
export const FETCH_MORE_TOPICS = 'FETCH_MORE_TOPICS'
export const CHANGE_TO_SEARCH_MODE = 'CHANGE_TO_SEARCH_MODE'
export const CHANGE_TO_INDEX_MODE = 'CHANGE_TO_INDEX_MODE'
export const UPDATE_TOPIC_LIST_ITEM = 'UPDATE_TOPIC_LIST_ITEM'
export const PREPEND_TOPIC = 'PREPEND_TOPIC'
export const EMPTY_TOPICS = 'EMPTY_TOPICS'

// search
export const FETCHING_SEARCH = 'FETCHING_SEARCH'
export const SEARCH = 'SEARCH'
export const FETCH_MORE_SEARCH = 'FETCH_MORE_SEARCH'
export const INPUT_KEYWORD = 'INPUT_KEYWORD'
export const SET_SEARCHING_KEYWORD = 'SET_SEARCHING_KEYWORD'
export const INITIALIZE_SEARCH = 'INITIALIZE_SEARCH'

export const FETCH_LATEST_MESSAGES = 'FETCH_LATEST_MESSAGES'
export const LOADING_LATEST_MESSAGES = 'LOADING_LATEST_MESSAGES'

export const SET_PUSHER_INFO = 'SET_PUSHER_INFO'
export const SET_UA_INFO = 'SET_UA_INFO'

export const TopicCreate = Object.freeze({
  SAVING: 'TopicCreate/SAVING',
  SAVE_SUCCESS: 'TopicCreate/SAVE_SUCCESS',
  SAVE_ERROR: 'TopicCreate/SAVE_ERROR',
  UPDATE_INPUT_DATA: 'TopicCreate/UPDATE_INPUT_DATA',
  RESET_STATES: 'TopicAddMembers/RESET_STATES',
})

export const TopicAddMembers = Object.freeze({
  SET_RESOURCE_ID: 'TopicAddMembers/SET_RESOURCE_ID',
  SAVING: 'TopicAddMembers/SAVING',
  SAVE_SUCCESS: 'TopicAddMembers/SAVE_SUCCESS',
  SAVE_ERROR: 'TopicAddMembers/SAVE_ERROR',
  SELECT_USERS: 'TopicAddMembers/SELECT_USERS',
  RESET_STATES: 'TopicAddMembers/RESET_STATES',
})
