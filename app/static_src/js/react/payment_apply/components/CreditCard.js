/* eslint-disable no-unused-vars */
import React from 'react'
/* eslint-enable no-unused-vars */
import {browserHistory, Link} from "react-router";
import * as Page from "~/payment_apply/constants/Page";
import Base from "~/common/components/Base";
import InvalidMessageBox from "~/common/components/InvalidMessageBox";
import ConfirmCharge from "~/common/components/payment/ConfirmCharge";
import LoadingButton from "~/common/components/LoadingButton";

export default class CreditCard extends Base {
  constructor(props) {
    super(props);
    this.state = {
      card: {},
      card_holder_name: "",
    };
    this.onChange = this.onChange.bind(this)
  }

  componentWillMount() {
    this.props.fetchInitialData(Page.CREDIT_CARD)
  }

  componentDidMount() {
    super.componentDidMount.apply(this)

    // Setup Stripe
    const stripe = Stripe(cake.stripe_publishable_key);
    let elements = stripe.elements();
    // TODO.Payment: componentize credit_card_form.js
    // Stripe Elements API
    // Custom styling can be passed to options when creating an Element.
    // Check: https://stripe.com/docs/stripe.js#elements-create
    let card = elements.create('card', {
      hidePostalCode: true,
      style: {
        base: {
          iconColor: '#808080',
          color: '#808080',
          lineHeight: '40px',
          fontWeight: 300,
          fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
          fontSize: '15px',
          '::placeholder': {
            color: '#808080',
          },
        },
      },
    });
    card.mount('#card-element');
    this.setState({card});
    this.props.initStripe(stripe)
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.payment.to_next_page) {
      browserHistory.push(Page.URL_COMPLETE)
    }
  }

  componentWillUnmount() {
    this.props.resetStates();
    super.componentWillUnmount.apply(this)
  }

  onChange(e) {
    const name = e.target.getAttribute('name');
    this.setState({[name]: e.target.value})
  }

  onSubmit(e) {
    e.preventDefault();
    const extra_details = {
      name: this.state.card_holder_name,
    };
    this.props.savePaymentCc(this.state.card, extra_details)
  }

  render() {
    const {payment} = this.props;
    const {validation_errors, error_message} = payment;
    let tax = payment.tax;
    let sub_total_charge = payment.sub_total_charge;
    let total_charge = payment.total_charge;
    let max_members = 0;
    if (payment.is_campaign_team) {
      const input_campaign = payment.selected_price_plan;
      tax = input_campaign.tax;
      sub_total_charge = input_campaign.sub_total_charge;
      total_charge = input_campaign.total_charge;
      max_members = input_campaign.max_members;

    }

    return (
      <section className="panel payment enter-cc-info">
          <form id="enterCCInfo" className="form-horizontal" name=""
                onSubmit={(e) => this.onSubmit(e)}>
          <div className="panel-container">
            <h3>{__('Enter your card information')}</h3>
            <div className="form-group">
              <label htmlFor="cardholder-name">{__('Name on Card')}</label>
              <input
                name="card_holder_name"
                className="form-control" placeholder="Jane Doe"
                onChange={this.onChange}/>
              <InvalidMessageBox message={validation_errors.name}/>
            </div>
            <div className="form-group">
              <label>{__('Card Number')}</label>
              <div id="card-element" className="form-control cc-field"></div>
              <InvalidMessageBox message={error_message}/>
            </div>
            <ConfirmCharge
              amount_per_user={payment.amount_per_user}
              charge_users_count={payment.charge_users_count}
              sub_total_charge={sub_total_charge}
              tax={tax}
              total_charge={total_charge}
              is_campaign={payment.is_campaign_team}
              max_members={max_members}
            />
          </div>
          <div className="panel-footer setting_pannel-footer">
            <Link className="btn btn-link design-cancel bd-radius_4px" to="/payments/apply/company">
              {__("Back")}
            </Link>
            {(() => {
              if (payment.is_saving) {
                return <LoadingButton/>
              } else {
                return (
                  <button
                    className="btn btn-primary"
                    disabled={payment.is_disabled_submit ? "disabled" : ""}>
                    {payment.is_campaign_team ? __("Agree & Purchase") : __("Purchase")}
                  </button>
                )
              }
            })()}
          </div>
        </form>
      </section>
    )
  }
}
