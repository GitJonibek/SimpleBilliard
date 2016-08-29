import React from 'react'
import ReactDOM from 'react-dom'
import { DisabledNextButton } from './elements/disabled_next_btn'
import { EnabledNextButton } from './elements/enabled_next_btn'
import { AlertMessageBox } from './elements/alert_message_box'
import { InvalidMessageBox } from './elements/invalid_message_box'
import { range, _checkValue } from '../actions/common_actions'

export default class UserName extends React.Component {

  getInputDomData() {
    return {
      first_name: ReactDOM.findDOMNode(this.refs.first_name).value.trim(),
      last_name: ReactDOM.findDOMNode(this.refs.last_name).value.trim(),
      birth_year: ReactDOM.findDOMNode(this.refs.birth_year).value.trim(),
      birth_month: ReactDOM.findDOMNode(this.refs.birth_month).value.trim(),
      birth_day: ReactDOM.findDOMNode(this.refs.birth_day).value.trim(),
      privacy: ReactDOM.findDOMNode(this.refs.privacy).checked,
      update_email_flg: ReactDOM.findDOMNode(this.refs.update_email_flg).checked
    }
  }

  handleSubmit(e) {
    e.preventDefault()
    this.props.postUserName(this.getInputDomData())
  }

  handleOnChange(e) {
    const validate_result = _checkValue(e.target)
    const element = { validate: {}, messages: {} }

    if(validate_result.error && validate_result.messages) {
      element['validate'][e.target.name] = false
      element['messages'] = validate_result.messages
      this.props.invalid(element)
    } else {
      element['validate'][e.target.name] = true
      element['messages'][e.target.name] = ''
      this.props.valid(element)
    }
  }

  handleBirthdayOnChange() {
    const birth_year = ReactDOM.findDOMNode(this.refs.birth_year).value.trim()
    const birth_month = ReactDOM.findDOMNode(this.refs.birth_month).value.trim()
    const birth_day = ReactDOM.findDOMNode(this.refs.birth_day).value.trim()
    const validate_result = _checkValue({name: 'birth_day', value: `${birth_year}-${birth_month}-${birth_day}`})

    if(validate_result.error && validate_result.messages) {
      element.validate.birth_day = false
      element.messages = validate_result.messages
      this.props.invalid(element)
    } else {
      element.validate.birth_day = true
      element.messages.birth_day = ''
      this.props.valid(element)
    }

  }

  render() {
    return (
      <div>
        <div className="row">
            <div className="panel panel-default panel-signup">
                <div className="panel-heading signup-title">{__("What's your name?")}</div>
                <img src="/img/signup/user.png" className="signup-header-image" />
                <div className="signup-description">{__("Your name will only be displayed to your team on Goalous.")}</div>

                <form className="form-horizontal" acceptCharset="utf-8"
                      onSubmit={ (e) => this.handleSubmit(e) }>

                    {/* First name */}
                    <div className="panel-heading signup-itemtitle">{__("Your name")}</div>
                    <div className={(this.props.user_name.invalid_messages.first_name) ? 'has-error' : ''}>
                      <input ref="first_name" name="first_name" className="form-control signup_input-design" type="text"
                             placeholder={__("eg. Harry")}
                             onChange={this.handleOnChange.bind(this)} />
                    </div>
                    <InvalidMessageBox is_invalid={this.props.user_name.user_name_is_invalid}
                                       message={this.props.user_name.invalid_messages.first_name} />

                    {/* Last name */}
                    <div className={(this.props.user_name.invalid_messages.last_name) ? 'has-error' : ''}>
                      <input ref="last_name" name="last_name" className="form-control signup_input-design"
                             placeholder={__("eg. Armstrong")} type="text"
                             onChange={this.handleOnChange.bind(this)} />
                    </div>
                    <InvalidMessageBox is_invalid={this.props.user_name.user_name_is_invalid}
                                       message={this.props.user_name.invalid_messages.last_name} />

                    {/* Allow Email from goalous check */}
                    <div className="signup-checkbox-email-flg">
                        <input type="checkbox" className="signup-checkbox-input" value="1" ref="update_email_flg"
                               checked="checked" />
                        <div className="signup-privacy-policy-label">
                          {__("I want to receive news and updates by email from Goalous.")}
                        </div>
                    </div>

                    {/* Birthday*/}
                    <div className="panel-heading signup-itemtitle">{__("Birthday")}</div>
                    <div className="form-inline signup_inputs-inline">
                        {/* Birthday year */}
                        <select className="form-control inline-fix" ref="birth_year" ref="birth_year" required
                                onChange={this.handleBirthdayOnChange}>
                           <option value=""></option>
                           {
                             range(1910, new Date().getFullYear()).sort((a,b) => b-a).map( year => {
                               return <option value={year} key={year}>{year}</option>;
                             })
                           }
                        </select>
                        &nbsp;/&nbsp;

                        {/* Birthday month */}
                        <select className="form-control inline-fix" ref="birth_month" name="birth_month" required
                                onChange={this.handleBirthdayOnChange}>
                           <option value=""></option>
                           <option value="01">{__("Jan")}</option>
                           <option value="02">{__("Feb")}</option>
                           <option value="03">{__("Mar")}</option>
                           <option value="04">{__("Apr")}</option>
                           <option value="05">{__("May")}</option>
                           <option value="06">{__("Jun")}</option>
                           <option value="07">{__("Jul")}</option>
                           <option value="08">{__("Aug")}</option>
                           <option value="09">{__("Sep")}</option>
                           <option value="10">{__("Oct")}</option>
                           <option value="11">{__("Nov")}</option>
                           <option value="12">{__("Dec")}</option>
                        </select>
                        &nbsp;/&nbsp;

                        {/* Birthday day */}
                        <select className="form-control inline-fix" ref="birth_day" name="birth_day" required
                                onChange={this.handleBirthdayOnChange}>
                           <option value=""></option>
                           {
                             range(1, 31).map( day => {
                               return <option value={day} key={day}>{day}</option>;
                             })
                           }
                        </select>

                        <InvalidMessageBox is_invalid={this.props.user_name.user_name_is_invalid}
                                           message={this.props.user_name.invalid_messages.birth_day} />
                    </div>

                    {/* Privacy policy check */}
                    <div className="signup-checkbox">
                        <input type="checkbox" value="1" className="signup-checkbox-input" ref="privacy" name="privacy"
                               onChange={this.handleOnChange.bind(this)} />
                        <div className="signup-privacy-policy-label" dangerouslySetInnerHTML={{__html: __("I agree to %s and %s of Goalous.", '<a href="/terms" target="_blank" className="signup-privacy-policy-link">term</a><br />', '<a href="/privacy_policy" target="_blank" className="signup-privacy-policy-link">Privacy Policy</a>')}}>
                        </div>
                    </div>

                    {/* Alert message */}
                    { (() => { if(this.props.user_name.is_exception) {
                      return <AlertMessageBox message={ this.props.user_name.exception_message } />;
                    }})() }

                    {/* Submit button */}
                    { (() => { if(this.props.user_name.submit_button_is_enabled) {
                      return <EnabledNextButton />;
                    } else {
                      return <DisabledNextButton loader={ this.props.user_name.checking_user_name } />;
                    }})() }

                </form>
            </div>
        </div>
      </div>
    )
  }
}
