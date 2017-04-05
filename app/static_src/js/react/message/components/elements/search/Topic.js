import React from 'react'
import AvatarsBox from "~/common/components/AvatarsBox"
import { setTopicOnDetail } from '~/message/actions/search'
import { updateTopicListItem } from '~/message/actions/index'
import { Link } from "react-router"
import { connect } from "react-redux"

class Topic extends React.Component {
  onClickLinkToDetail() {
    const { dispatch, topic, index } = this.props
    dispatch(updateTopicListItem(index, { is_unread: false }))
    dispatch(setTopicOnDetail(topic))
  }

  render() {
    const topic = this.props.topic
    return (
      <li className="topicSearchList-item" key={ topic.id }>
        <Link to={ `/topics/${topic.id}/detail` }
              className="topicSearchList-item-link"
              onClick={ this.onClickLinkToDetail.bind(this) }>
          <AvatarsBox users={ topic.users } />
          <div className="topicSearchList-item-main">
            <div className="topicSearchList-item-main-header">
              <div className="topicSearchList-item-main-header-title oneline-ellipsis">
                { topic.display_title }
              </div>
              <div className="topicSearchList-item-main-header-count">
              </div>
            </div>
            <div className="topicSearchList-item-main-body oneline-ellipsis">
              { topic.latest_message.body }
            </div>
          </div>
          <div className="topicSearchList-item-right">
            { topic.latest_message.display_created }
          </div>
        </Link>
      </li>
    )
  }
}

export default connect()(Topic);
