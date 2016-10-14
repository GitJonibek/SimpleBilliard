import * as types from '../constants/ActionTypes'

const initialState = {
  fetch_data: {
    collaborators: [],
    all_approval_count: 0,
    application_info: ''
  },
  fetching_collaborators: false,
  next_getting_api: null,
  // TODO: 第一フェーズではページネーションは行わないので全件表示する
  done_loading_all_data: true
}

export default function list(state = initialState, action) {
  switch (action.type) {
    case types.SET_FETCH_DATA:
      return Object.assign({}, state, {
        fetch_data: action.fetch_data
      })
    case types.FETCHING_COLLABORATORS:
      return Object.assign({}, state, {
        fetching_collaborators: true
      })
    case types.FINISHED_FETCHING_COLLABORATORS:
      return Object.assign({}, state, {
        fetching_collaborators: false
      })
    default:
      return state;
  }
}
