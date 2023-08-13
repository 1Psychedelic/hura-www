import React from 'react'

const ReservationLoading = (props) => {
    return (
        <>
            <section className="page__section page__section--generic-heading ph-item">
                <div className="page__section__content">
                    <span className="page__section__subheading">&nbsp;</span>
                    <h2 className="page__section__heading">&nbsp;</h2>
                </div>
            </section>
            <section className="page__section page__section--reservation-detail">
                <div ref={props.reservationFormTop} className="page__section__content">

                    <div className="reservation-form-wrapper">
                        <div className="reservation-form-sidebar ph-item"/>
                        <div className="reservation-form">
                            <h3>Načítám...</h3>
                            <div className="reservation-form-subheading">
                                <span>Počkejte prosím...</span>
                            </div>
                            <div className="form-half-width-container">
                                <div className="form-half-width">
                                    <div className="form-input-group form-input-group-text">
                                        <div className="form-input-group-inner ph-item">
                                            <input type="text"/>
                                        </div>
                                        <div className="form-error-message">&nbsp;</div>
                                    </div>
                                    <div className="form-input-group form-input-group-text">
                                        <div className="form-input-group-inner ph-item">
                                            <input type="text"/>
                                        </div>
                                        <div className="form-error-message">&nbsp;</div>
                                    </div>
                                    <div className="form-input-group form-input-group-text">
                                        <div className="form-input-group-inner ph-item">
                                            <input type="text"/>
                                        </div>
                                        <div className="form-error-message">&nbsp;</div>
                                    </div>
                                </div>
                                <div className="form-half-width">
                                    <div className="form-input-group form-input-group-text">
                                        <div className="form-input-group-inner ph-item">
                                            <input type="text"/>
                                        </div>
                                        <div className="form-error-message">&nbsp;</div>
                                    </div>
                                    <div className="form-input-group form-input-group-text">
                                        <div className="form-input-group-inner ph-item">
                                            <input type="text"/>
                                        </div>
                                        <div className="form-error-message">&nbsp;</div>
                                    </div>
                                    <div className="form-input-group form-input-group-text">
                                        <div className="form-input-group-inner ph-item">
                                            <input type="text"/>
                                        </div>
                                        <div className="form-error-message">&nbsp;</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </section>
        </>
    );
};

export default ReservationLoading
