import React from "react";

export default class NoGoal extends React.Component {
  render() {
    return (
      <div className="panel-block bd-b-sc4">
        <p className="text-align_c mtb_8px">
          {__("No Goals found")}
        </p>
      </div>
    )
  }
}

