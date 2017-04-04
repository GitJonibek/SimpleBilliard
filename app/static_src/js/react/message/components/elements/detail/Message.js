import React from "react";
import {nl2br} from "~/util/element";
import AttachedFile from "~/message/components/elements/detail/AttachedFile";


export default class Message extends React.Component {
  constructor(props) {
    super(props);
  }

  render() {
    const {topic, message, is_last_idx} = this.props
    const read_mark_el = () => {
      if (!is_last_idx) {
        return null;
      }
      const read_count = (topic.latest_message_id == message.id) ? topic.read_count : 0;

      const is_all_read = (read_count == topic.members_count - 1);
      if (is_all_read) {
        return (
          <div>
            <a href={`/topics/ajax_get_read_members/${topic.id}`}
               className="topicDetail-messages-item-read is-on modal-ajax-get">
              <i className="fa fa-check"/>
            </a>
          </div>
        )
      } else {
        return (
          <div>
            <a href={`/topics/ajax_get_read_members/${topic.id}`}
               className="topicDetail-messages-item-read is-off modal-ajax-get">
              <i className="fa fa-check mr_2px"/>
              {read_count}
              <span className="ml_5px topicDetail-messages-item-read-update">{__("Update")}</span>
            </a>
          </div>
        )
      }
    }

    return (
      <div className="topicDetail-messages-item">
        <div className="topicDetail-messages-item-left">
          <a href={`/users/view_goals/user_id:${message.user.id}`}
             className="topicDetail-messages-item-left-profileImg">
            <img className="lazy"
                 src={message.user.medium_img_url}/>
          </a>
        </div>
        <div className="topicDetail-messages-item-right">
          <div className>
            <span className="topicDetail-messages-item-userName">
              {message.user.display_username}
            </span>
            <span className="topicDetail-messages-item-datetime">
              {message.display_created}
            </span>
          </div>
          <p className="topicDetail-messages-item-content">
            {message.body === "[like]" ?
              <i className="fa fa-thumbs-o-up font_brownRed"></i>
              : nl2br(message.body)
            }
          </p>
          {message.attached_files.map((attached_file) => {
            return (
              <AttachedFile
                key={attached_file.id}
                attached_file={attached_file}/>
            )
          })}
          {read_mark_el()}
        </div>
      </div>
    )
  }
}
Message.propTypes = {
  message: React.PropTypes.object,
  is_last_idx: React.PropTypes.bool,
};

Message.defaultProps = {
  message: {},
  is_last_idx: 0,
};
