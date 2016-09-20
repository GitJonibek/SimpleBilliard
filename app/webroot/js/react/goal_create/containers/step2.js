import {connect} from "react-redux";
import Step2Component from "../components/step2";
import * as actions from "../actions/goal_actions";
import * as common from "./common";

function mapStateToProps(state) {
  return state
}

function mapDispatchToProps(dispatch) {
  return Object.assign({}, common.getCommonDispatchToProps(dispatch), {
    onChangeAutoSuggest(event, {newValue}) {
      dispatch(actions.setKeyword(newValue));
    },
    onSuggestionsFetchRequested: ({value}) => dispatch(actions.onSuggestionsFetchRequested(value)),
    onSuggestionsClearRequested: () => dispatch(actions.onSuggestionsClearRequested()),
    onSuggestionSelected: (e, {suggestion}) => dispatch(actions.onSuggestionSelected(suggestion)),
    deleteLabel: (label) => dispatch(actions.deleteLabel(label)),
    addLabel: (label) => dispatch(actions.addLabel(label)),
  });
}

export default connect(mapStateToProps, mapDispatchToProps)(Step2Component)
