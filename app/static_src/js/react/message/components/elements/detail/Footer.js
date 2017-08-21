import React from "react";
import ReactDom from "react-dom";
import {connect} from "react-redux";
import * as detail from "~/message/actions/detail";
import * as file_upload from "~/message/modules/file_upload";
import UploadDropZone from "~/message/components/elements/detail/UploadDropZone";
import UploadPreview from "~/message/components/elements/detail/UploadPreview";
import LoadingButton from "~/common/components/LoadingButton";
import {SaveMessageStatus} from "~/message/constants/Statuses";
import {PositionIOSApp, PositionMobileApp} from "~/message/constants/Styles";
import Textarea from "react-textarea-autosize";
import {nl2br} from "~/util/element";
import {isIOSApp, isMobileApp} from "~/util/base";
import {HotKeys} from "react-hotkeys";

class Footer extends React.Component {
  constructor(props) {
    super(props)

    // HACK:Display drop zone when dragging
    // reference:http://qiita.com/sounisi5011/items/dc4878d3e8b38101cf0b
    this.state = {
      is_drag_over: false,
      is_drag_start: false,
    }
    this.sendMessage = this.sendMessage.bind(this);
    this.onTouchMove = this.onTouchMove.bind(this)
  }

  componentDidMount() {
    if (!isMobileApp()) {
      return;
    }
    const body_bottom = ReactDom.findDOMNode(this.refs.topic_detail_footer).offsetHeight;
    this.props.dispatch(
      detail.changeLayout({body_bottom})
    );
  }

  sendLike(e) {
    this.props.dispatch(
      detail.sendLike()
    );
  }

  sendMessage(e) {
    if (this.props.body || this.props.uploaded_file_ids.length > 0) {
      this.props.dispatch(
        detail.sendMessage()
      );
    }
  }

  inputMessage(e) {

    this.props.dispatch(
      detail.inputMessage(e.target.value)
    );

    if (!isIOSApp()) {
      return;
    }
    // TODO: Scroll by document height
    window.scrollTo(0, 1000)
    const body_bottom = ReactDom.findDOMNode(this.refs.topic_detail_footer).offsetHeight;
    this.props.dispatch(
      detail.changeLayout({body_bottom})
    );
  }

  uploadFiles(files) {
    if (!files) {
      return;
    }

    this.props.dispatch(
      file_upload.uploadFiles(files)
    );
  }

  dragEnter(e) {
    e.stopPropagation();
    e.preventDefault();
    this.setState({is_drag_start: true});
  }

  dragOver(e) {
    e.stopPropagation();
    e.preventDefault();
    this.setState({is_drag_over: true});
  }

  dragLeave(e) {
    if (this.state.is_drag_start) {
      this.setState({is_drag_start: false});
    } else {
      this.setState({is_drag_over: false});
    }
  }

  drop(e) {
    e.stopPropagation();
    e.preventDefault();

    this.setState({is_drag_over: false});

    const files = e.dataTransfer.files;

    this.uploadFiles(files);
  }

  selectFile(e) {
    ReactDom.findDOMNode(this.refs.file).click();
  }

  changeFile(e) {
    const files = e.target.files;
    this.uploadFiles(files);
  }

  focusInputBody(e) {
    if (isIOSApp()) {
      this.props.dispatch(
        detail.changeLayout({
          body_bottom: PositionMobileApp.BODY_BOTTOM,
          footer_bottom: PositionMobileApp.FOOTER_BOTTOM,
        })
      );
    }
  }

  blurInputBody(e) {
    if (isIOSApp()) {
      this.props.dispatch(
        detail.changeLayout({
          body_bottom: PositionIOSApp.BODY_BOTTOM,
          footer_bottom: PositionIOSApp.FOOTER_BOTTOM,
        })
      );
    }
  }

  onTouchMove(e) {
    e.preventDefault()
  }

  render() {
    const sp_class = this.props.is_mobile_app ? "mod-sp" : "";
    const footer_style = {
      bottom: this.props.mobile_app_layout.footer_bottom
    }

    const key_map = {
      'sendMessage': ['command+enter', 'ctrl+enter']
    };
    const handlers = {
      'sendMessage': (e) => this.sendMessage(e),
    };

    return (
      <div
        className={`topicDetail-footer ${sp_class} ${this.state.is_drag_over && "uploadFileForm-wrapper"}`}
        onDrop={this.drop.bind(this)}
        onDragEnter={this.dragEnter.bind(this)}
        onDragOver={this.dragOver.bind(this)}
        onDragLeave={this.dragLeave.bind(this)}
        onTouchMove={this.onTouchMove}
        style={footer_style}
        ref="topic_detail_footer"
      >
        {this.state.is_drag_over && <UploadDropZone/>}
        <UploadPreview files={this.props.preview_files}/>
        <form>
          <div className="topicDetail-footer-box">
            <div className="topicDetail-footer-box-left">
              <span
                className="btn btnRadiusOnlyIcon mod-upload"
                onClick={this.selectFile.bind(this)}
              />
              {/* For avoiding ios bug, only in ios app, not setting multiple attr
                  ref) https://jira.goalous.com/browse/GL-5732 */}
              <input type="file" multiple={isIOSApp() ? '' : 'multiple'} className="hidden" ref="file"
                     onChange={this.changeFile.bind(this)}/>
            </div>
            <div className="topicDetail-footer-box-center">
              <HotKeys keyMap={key_map} handlers={handlers}>
                <Textarea
                  className={`topicDetail-footer-inputBody form-control disable-change-warning ${sp_class}`}
                  rows={1} cols={30} placeholder={__("Reply")}
                  name="message_body" value={this.props.body}
                  onInput={this.inputMessage.bind(this)}
                  onFocus={this.focusInputBody.bind(this)}
                  onBlur={this.blurInputBody.bind(this)}
                />
              </HotKeys>
              {this.props.err_msg &&
              <div className="has-error">
                <p className="has-error help-block">
                  {nl2br(this.props.err_msg)}
                </p>
              </div>
              }
            </div>
            <div className="topicDetail-footer-box-right">
              {(() => {
                if (this.props.save_message_status == SaveMessageStatus.SAVING) {
                  return <LoadingButton/>
                }

                if (this.props.body || this.props.uploaded_file_ids.length > 0) {
                  return (
                    <span
                      className="btn btnRadiusOnlyIcon mod-send"
                      onClick={this.sendMessage}
                      disabled={this.props.is_uploading && "disabled"}/>
                  )
                } else {
                  return (
                    <span
                      className="btn btnRadiusOnlyIcon mod-like"
                      onClick={this.sendLike.bind(this)}
                      disabled={(this.props.is_uploading) && "disabled"}/>
                  )
                }
              })(this)}
            </div>
          </div>
        </form>
      </div>
    )
  }
}

Footer.propTypes = {
  body: React.PropTypes.string,
  uploaded_file_ids: React.PropTypes.array,
  preview_files: React.PropTypes.array,
  save_message_status: React.PropTypes.number,
  is_uploading: React.PropTypes.bool,
  err_msg: React.PropTypes.string,
  is_mobile_app: React.PropTypes.bool,
};
Footer.defaultProps = {
  body: "",
  uploaded_file_ids: [],
  preview_files: [],
  save_message_status: SaveMessageStatus.NONE,
  is_uploading: false,
  err_msg: "",
  is_mobile_app: false,
};
export default connect()(Footer);
