import * as types from '../constants/ActionTypes'

const initialState = {
  inputed: {
    first_name: '',
    last_name: '',
    local_first_name: '',
    local_last_name: ''
  },
  checking_user_name: false,
  submit_button_is_enabled: false,
  user_name_is_invalid: false,
  invalid_messages: {}
}

export default function term(state = initialState, action) {
  switch (action.type) {
    case types.INPUT_USER_NAME:
      return Object.assign({}, state, {
        inputed: action.inputed
      })
    case types.CHECKING_USER_NAME:
      return Object.assign({}, state, {
        checking_user_name: true
      })
    case types.FINISHED_CHECKING_USER_NAME:
      return Object.assign({}, state, {
        checking_user_name: false
      })
    case types.USER_NAME_IS_INVALID:
      return Object.assign({}, state, {
        user_name_is_invalid: true,
        invalid_messages: action.invalid_messages
      })
    case types.USER_NAME_IS_VALID:
      return Object.assign({}, state, {
        user_name_is_invalid: false
      })
    case types.CAN_SUBMIT_USER_NAME:
      return Object.assign({}, state, {
        submit_button_is_enabled: true
      })
    case types.CAN_NOT_SUBMIT_USER_NAME:
      return Object.assign({}, state, {
        submit_button_is_enabled: false
      })
    default:
      return state;

  }

}
