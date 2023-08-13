import React from 'react'


const ReservationFormSidebarChildren = (props) => {
    let createSidebarChild = (child) => {
        return (
            <a href="#" onClick={(event) => {event.preventDefault();if (!child.isInReservation) {props.onChildAddToReservation(child.childId);}}} key={child.childId} className={'reservation-form-sidebar-child' + (child.isInReservation ? ' active' : '')}>
                <div className="reservation-form-sidebar-child-info">
                    <div className="reservation-form-sidebar-child-name">{child.name}</div>
                    <div className="reservation-form-sidebar-child-age">Věk: {child.dateBorn}</div>
                </div>
                <div className="reservation-form-sidebar-child-icon">
                </div>
            </a>
        );
    };

    let createSidebarChildren = (children) => {
        return children.map(createSidebarChild);
    };

    if (props.children.length === 0) {
        return (
            <div className="reservation-form-sidebar">
                <h3>Vaše děti</h3>
                <div className="reservation-form-sidebar-subheading">
                    <span>Zde se bude zobrazovat seznam Vašich dětí v profilu. Na budoucí akce je přihlásíte jednoduše jedním kliknutím!</span>
                </div>
            </div>
        );
    }

    return (
        <div className="reservation-form-sidebar">
            <h3>Vaše děti</h3>
            <div className="reservation-form-sidebar-subheading">
                <span>Kliknutím je přihlásíte</span>
            </div>

            <div className="reservation-form-sidebar-children">
                {createSidebarChildren(props.children)}
            </div>

        </div>
    )
};

export default ReservationFormSidebarChildren
