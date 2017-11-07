import * as types from "../constants/ActionTypes";

const initialState = {
  to_next_page: false,
  team:{},
  emails:[],
  validation_errors: {
  },
  confirm_data: {},
  input_data: {
    emails: "",
  },
  is_saving: false,
  redirect_to_home: false,
  redirect_to_upgrade_plan: false
}

export default function invite(state = initialState, action) {
  let input_data = state.input_data
  switch (action.type) {
    case types.INVALID:
      return Object.assign({}, state, {
        validation_errors: action.error.validation_errors,
        is_saving: false
      })
    case types.TO_NEXT_PAGE:
      return Object.assign({}, state, {
        emails: action.data.emails,
        to_next_page: true,
      })
    case types.FETCH_INPUT_INITIAL_DATA:
      return Object.assign({}, state, {
        team: action.data.team,
        to_next_page: false,
        validation_errors: {}
      })
    case types.FETCH_CONFIRM_INITIAL_DATA:
      return Object.assign({}, state, {
        confirm_data: action.data,
        to_next_page: false,
        validation_errors: {}
      })

    case types.UPDATE_INPUT_DATA:
      input_data = Object.assign({}, input_data, action.input_data)
      return Object.assign({}, state, {
        input_data
      })
    case types.REDIRECT_TO_HOME:
      return Object.assign({}, state, {
        redirect_to_home: true,
      })
    case types.REDIRECT_TO_UPGRADE_PLAN:
      return Object.assign({}, state, {
          redirect_to_upgrade_plan: true,
      })
    case types.SAVING:
      return Object.assign({}, state, {
        is_saving: true
      })

    default:
      return state;
  }
}
