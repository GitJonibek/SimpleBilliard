import React from 'react'
import { Link, browserHistory } from 'react-router'

export default class CircleCreate extends React.Component {
  constructor(props, context) {
    super(props, context);
  }
  handleSubmit() {

  }
  render() {
    return (
      <div>
        <div className="setup-pankuzu font_18px">
          {__("Set up Goalous")} <i className="fa fa-angle-right" aria-hidden="true"></i> {__("Join a circle")}
        </div>
        <p className="font_bold font_verydark">{__("Create a new circle")}</p>
        <form onSubmit={e => {this.handleSubmit.bind(this)}} className="form-horizontal" encType="multipart/form-data" method="post" acceptCharset="utf-8">
          <div className="panel-body">
            <span className="help-block">{__("Circle Name")}</span>
            <input ref="circle_name" className="form-control addteam_input-design" />
          </div>
          <div className="panel-body">
            <span className="help-block">{__("Members")}</span>
            <input ref="members" className="form-control addteam_input-design" />
          </div>
          <div className="panel-body setup-circle-public-group">
              <input type="radio" name="public_flg" id="CirclePublicFlg1" value="1" checked="checked" /> {__("Public")}
              <span className="help-block font_11px">
                {__("Anyone can see the circle, its members and their posts.")}</span>
              <input type="radio" name="public_flg" id="CirclePublicFlg0" value="0" /> {__("Privacy")}
              <span className="help-block font_11px">
                {__("Only members can find the circle and see posts.")}
              </span>
          </div>
          <div className="panel-body">
            <span className="help-block">{__("Circle Description")}</span>
            <input ref="members" className="form-control addteam_input-design" />
          </div>
          <div className="panel-body">
            <span className="help-block">{__("Circle Image")}</span>
            <div className="form-inline">
              <div className="fileinput_small fileinput-new" data-provides="fileinput">
                <div className="fileinput-preview thumbnail nailthumb-container photo-design form-group" data-trigger="fileinput">
                  <i className="fa fa-plus photo-plus-large"></i>
                </div>
                <div className="form-group">
                  <span className="btn btn-default btn-file ml_16px">
                    <span className="fileinput-new">{__("Select an image")}</span>
                    <span className="fileinput-exists">{__("Reselect an image")}</span>
                    <input type="file" name="circle_image" className="form-control addteam_input-design" />
                  </span>
                </div>
              </div>
            </div>
          </div>
          <div>
            <Link to="/setup/circle/select" className="btn btn-secondary setup-back-btn">Back</Link>
            <input type="submit" className="btn btn-primary setup-next-btn pull-right" defaultValue={__("Create")} />
          </div>
        </form>
      </div>
    )
  }
}
