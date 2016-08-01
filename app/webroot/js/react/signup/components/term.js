import React from 'react'
import ReactDOM from 'react-dom'
import { DisabledNextButton } from './elements/disabled_next_btn'
import { EnabledNextButton } from './elements/enabled_next_btn'
import { AlertMessageBox } from './elements/alert_message_box'
import { InvalidMessageBox } from './elements/invalid_message_box'

export default class Term extends React.Component {

  getInputDomData(ref_name) {
    return ReactDOM.findDOMNode(this.refs[ref_name]).value.trim()
  }

  handleSubmit(e) {
    e.preventDefault()
    this.props.postTerms()
  }

  render() {
    return (
      <div className="row">
          <div className="panel panel-default panel-signup">
              <div className="panel-heading signup-title">Finaly, Teams Term?</div>
              <div className="signup-description">Teams Term sample text Teams Term sample text Teams Term sample text Teams Term sample text.</div>

              <form className="form-horizontal" acceptCharset="utf-8"
                    onSubmit={(e) => this.handleSubmit(e) } >

                  <div className="panel-heading signup-itemtitle">Term</div>
                  <select className="form-control signup_input-design" ref="term" onChange={ () => { this.props.selectTerm(this.getInputDomData('term')) } }>
                      <option value="">選択してください</option>
                      <option value="3">四半期</option>
                      <option value="6">半年</option>
                      <option value="12">年</option>
                  </select>

                  <div className="panel-heading signup-itemtitle">Select your present term ?</div>
                  <select className="form-control signup_input-design" ref="start_month" onChange={ () => { this.props.selectStartMonth(this.getInputDomData('start_month')) } }>
                      <option value="">選択してください</option>
                      <option value="1">１月</option>
                      <option value="2">２月</option>
                      <option value="3">３月</option>
                      <option value="4">４月</option>
                      <option value="5">５月</option>
                      <option value="6">６月</option>
                      <option value="7">７月</option>
                      <option value="8">８月</option>
                      <option value="9">９月</option>
                      <option value="10">１０月</option>
                      <option value="11">１１月</option>
                      <option value="12">１２月</option>
                  </select>

                  {/* Timezone */}
                  <div className="panel-heading signup-itemtitle">Timezone</div>
                  { (() => { if(this.props.term.is_timezone_edit_mode) {
                    let timezone_options = []

                    for(const key in cake.data.timezones) {
                      timezone_options.push(<option value={key} key={cake.data.timezones[key]}>{cake.data.timezones[key]}</option>);
                    }
                    return (
                      <select className="form-control signup_input-design" defaultValue="+9.0" ref="timezone"
                              onChange={ () => { this.props.selectTimezone(this.getInputDomData('timezone')) } }>
                        {timezone_options}
                      </select>
                    )
                  } else {
                    return (
                      <p className="signup-timezone-label-wrapper">
                          <span className="signup-goal-timezone-label">(GMT+9:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk</span>
                          <a href="#" onClick={ () => { this.props.changeToTimezoneSelectMode() } }>変更する</a>
                      </p>
                    )
                  }})() }

                  {/* Alert message */}
                  { (() => { if(this.props.term.is_exception) {
                    return <AlertMessageBox message={ this.props.term.exception_message } />;
                  }})() }

                  {/* Submit button */}
                  { (() => { if(this.props.term.submit_button_is_enabled) {
                    return <EnabledNextButton />;
                  } else {
                    return <DisabledNextButton loader={ this.props.term.checking_term } />;
                  }})() }

              </form>

          </div>
      </div>
    )
  }
}
