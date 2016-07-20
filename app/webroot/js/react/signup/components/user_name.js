import React from 'react'
import ReactDOM from 'react-dom'
import { Link } from 'react-router'

export default class UserName extends React.Component {

  getInputDomData() {
    return {
      first_name: ReactDOM.findDOMNode(this.refs.first_name).value.trim(),
      last_name: ReactDOM.findDOMNode(this.refs.last_name).value.trim(),
      local_first_name: ReactDOM.findDOMNode(this.refs.local_first_name).value.trim(),
      local_last_name: ReactDOM.findDOMNode(this.refs.local_last_name).value.trim()
    }
  }

  render() {
    const disabled_btn = () => {
      return (
        <div className="submit signup-btn">
            <input className="btn btn-lightGray signup-btn-submit" type="submit" value="Next→" disabled="disabled" />
        </div>
      )
    }
    const enabled_btn = () => {
      return (
        <div className="submit signup-btn">
            <input className="btn btn-primary signup-btn-submit" type="button" value="Next→"
                   onClick={ () => {
                     this.props.postUserName(this.getInputDomData())
                   }} />
        </div>
      )
    }
    const invalid_message_box = () => {
      return (
        <div className="signup-error-description">
            <i className="fa fa-exclamation-circle signup-load-icon mod-error"></i> 利用規約に同意してください。
        </div>
      )
    }

    return (
      <div>
        <div className="row">
            {/*<!-- START app/View/Signup/user.ctp -->*/}
            <div className="panel panel-default panel-signup">
                <div className="panel-heading signup-title">{"What's your name?"}</div>
                <div className="signup-description">username sample textusername sample text username sample text username sample text username sample text username sample text.</div>

                <form action="#" className="form-horizontal" id="" acceptCharset="utf-8">
                    <div className="panel-heading signup-itemtitle">your name</div>
                    <input ref="first_name" className="form-control signup_input-design" placeholder="例) Suzuki" type="text" />
                    <input ref="last_name" className="form-control signup_input-design" placeholder="例) Hanako" type="text" />
                    <input ref="local_first_name" className="form-control signup_input-design" placeholder="例) すずき" type="text" />
                    <input ref="local_last_name" className="form-control signup_input-design" placeholder="例) はなこ" type="text" />

                    <div className="checkbox signup-checkbox">
                        <input type="hidden" name="data[User][agree_tos]" id="UserAgreeTos_" value="0" />
                        <label>
                            <input type="checkbox" value="1" id="UserAgreeTos" /> Goalousの<Link to="/terms" target="_blank"
                                   className="link">利用規約</Link>と<Link to="/privacy_policy" target="_blank" className="link">プライバシーポリシー</Link>に同意します。</label>
                    </div>
                    { this.props.user_name.user_name_is_invalid || this.props.user_name.is_exception ? invalid_message_box() : '' }
                    { this.props.user_name.checking_user_name ? disabled_btn() : enabled_btn() }
                </form>
            </div>
        </div>
        {/*<!-- END app/View/Signup/user.ctp -->*/}
      </div>
    )
  }
}
