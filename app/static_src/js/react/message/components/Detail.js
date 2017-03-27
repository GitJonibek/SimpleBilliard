import React from "react";
import {Link} from "react-router";
import Header from "~/message/components/elements/detail/Header";
import Body from "~/message/components/elements/detail/Body";
import Footer from "~/message/components/elements/detail/Footer";

// TODO:Display loading during fetching initial data
export default class Detail extends React.Component {
  constructor(props) {
    super(props);
  }

  componentWillMount() {
    // Set resource ID included in url.
    this.props.setResourceId(this.props.params.topic_id);
    this.props.fetchInitialData(this.props.params.topic_id);
  }

  render() {
    const props = this.props.detail;

    return (
      <div className="panel panel-default topicDetail">
        <Header
          topic={props.topic}
          topic_title_setting_status={props.topic_title_setting_status}
          save_topic_title_err_msg={props.save_topic_title_err_msg}
        />
        <Body
          topic={props.topic}
          messages={props.messages.data}
          paging={props.messages.paging}
          loading_more={props.loading_more}
          is_fetched_initial={props.is_fetched_initial}
        />
        <Footer
          message={props.input_data.message}
          uploaded_file_ids={props.input_data.file_ids}
          files={props.files}
          err_msg={props.err_msg}
          is_saving={props.is_saving}
          is_uploading={props.is_uploading}
        />
      </div>
    )
  }
}
