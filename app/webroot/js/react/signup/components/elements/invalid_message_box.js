import React from 'react'

export class InvalidMessageBox extends React.Component {
  render() {
    return (
      <div className="has-error">
          <small className="help-block">{ (this.props.is_invalid && this.props.message) ? this.props.message : '' }</small>
      </div>
    )
  }
}
