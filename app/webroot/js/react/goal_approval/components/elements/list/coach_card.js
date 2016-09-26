import React from 'react'
import { Link } from 'react-router'

export class CoachCard extends React.Component {
  render() {
    const ApprovalStatus = Collaborator.ApprovalStatus
    const Type = Collaborator.Type
    const collaborator = this.props.collaborator
    const role = collaborator.type == Type.OWNER ? __('Leader') : __('Collaborator')
    const status = (() => {
      if(collaborator.is_target_evaluation) {
        return __('Evaluated')
      }
      if(collaborator.approval_status == ApprovalStatus.NEW) {
        return __('New')
      }
      if(collaborator.approval_status == ApprovalStatus.REAPPLICATION) {
        return __('Reapplication')
      }
      return __('Not Evaluated')
    })()

    return (
      <li className="goals-approval-list-item">
          <div className={`goals-approval-list-item ${ is_incomplete ? "is-incomplete" : "is-complete" }`}>
              <Link className="goals-approval-list-item-link" to={ `/goals/approval/detail/${collaborator.id}` }>
                  <img className="goals-approval-list-item-image" src={ collaborator.user.small_img_url } alt="" width="32" height="32" />

                  <div className="goals-approval-list-item-info">
                      <p className="goals-approval-list-item-info-user-name">{ collaborator.user.display_username }</p>
                      <p className="goals-approval-list-item-info-goal-name">{ collaborator.goal.name }</p>
                      <p className="goals-approval-list-item-info-goal-attr">{ role }・<span className="mod-status">{ status }</span></p>
                  </div>

                  <p className="goals-approval-list-item-detail">
                    <i className={`fa ${ is_incomplete ? "fa-angle-right" : "fa-check" }`} ariaHidden="true"></i>
                  </p>
              </Link>
          </div>
      </li>
    )
  }
}

CoachCard.propTypes = {
  collaborator: React.PropTypes.object.isRequired
}
