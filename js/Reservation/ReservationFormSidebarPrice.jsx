import React from 'react'
import ReservationFormSidebarPriceDiscounts from "./ReservationFormSidebarPriceDiscounts";


const ReservationFormSidebarPrice = (props) => {

    console.log({'reservationprice': props});

    let event = props.event;
    let reservation = props.reservation;

    let reservationChildren = reservation.children.filter((child) => child.isInReservation);

    let totalPrice = 0;
    let groups = [];

    if (reservationChildren.length > 0) {
        let subtotal = reservationChildren.length * props.event.price;
        totalPrice += subtotal;
        let childrenGroup = {
            'title': props.event.name,
            'items': [],
            'subtotal': subtotal
        };
        for (let i in reservationChildren) {
            childrenGroup.items.push({
                'title': reservationChildren[i].name,
                'price': props.event.price
            });
        }
        groups.push(childrenGroup);
    }

    if (reservation.addons) {
        let addonsGroup = {
            'title': 'Doplňkové služby',
            'items': [],
            'subtotal': 0
        };
        for (let addonId in reservation.addons) {
            let addonPrice = 0;
            let addonName = null;
            let addonQuantity = 0;
            for (let i in event.addons) {
                if (event.addons[i].id.toString() === addonId.toString()) {
                    addonName = event.addons[i].name;
                    addonQuantity = (Math.min(reservationChildren.length, Math.max(0, reservation.addons[addonId])));
                    addonPrice = event.addons[i].price * addonQuantity;
                }
            }

            if (addonQuantity > 0 && addonName !== null && addonPrice > 0) {
                addonsGroup.items.push({
                    'title': addonQuantity + '× ' + addonName,
                    'price': addonPrice
                });
                addonsGroup.subtotal += addonPrice;
                totalPrice += addonPrice;
            }
        }
        if (addonsGroup.items.length > 0) {
            groups.push(addonsGroup);
        }
    }

    if (reservation.discounts.payingByCredit > 0 || reservation.discounts.payingByDiscountCode > 0) {
        let discountsGroup = {
            'title': 'Slevy',
            'items': [],
            'subtotal': 0
        };

        if (reservation.discounts.payingByDiscountCode > 0) {
            discountsGroup.items.push({
                'title': 'Slevový kód "' + reservation.discounts.discountCode + '"',
                'price': -reservation.discounts.payingByDiscountCode
            });
            discountsGroup.subtotal -= reservation.discounts.payingByDiscountCode;
            totalPrice -= reservation.discounts.payingByDiscountCode;
        }

        if (reservation.discounts.payingByCredit > 0) {
            let creditDiscount = Math.min(totalPrice, reservation.discounts.payingByCredit);
            discountsGroup.items.push({
                'title': 'Platba kreditem',
                'price': -creditDiscount
            });
            discountsGroup.subtotal -= creditDiscount;
            totalPrice -= creditDiscount;
        }

        groups.push(discountsGroup);
    }

    let createPriceGroupItem = (groupItem) => {
        return (
            <div key={groupItem.title} className="reservation-sidebar-price-group-item">
                <div className="reservation-sidebar-price-group-item-title">
                    {groupItem.title}
                </div>
                <div className="reservation-sidebar-price-group-item-price">
                    {groupItem.price.toLocaleString('cs-CZ') + " Kč"}
                </div>
            </div>
        );
    };

    let createPriceGroupItems = (groupItems) => {
        return groupItems.map(createPriceGroupItem);
    };

    let createPriceGroup = (group) => {
        return (
            <div key={group.title} className="reservation-sidebar-price-group">
                <h4>{group.title}</h4>
                {createPriceGroupItems(group.items)}
                <div className="reservation-sidebar-price-group-subtotal">
                    {group.subtotal.toLocaleString('cs-CZ') + " Kč"}
                </div>
            </div>
        );
    };

    let createPriceGroups = (groups) => {
        return groups.map(createPriceGroup);
    };

    return (
        <div className="reservation-form-sidebar">
            <h3>Cena celkem</h3>
            <div className="reservation-form-sidebar-subheading">
                <span>včetně DPH</span>
            </div>

            <div className="reservation-form-sidebar-price">
                {createPriceGroups(groups)}
                <div className="reservation-form-sidebar-price-total">
                    Celkem: <span>{totalPrice.toLocaleString('cs-CZ') + " Kč"}</span>
                </div>

                <ReservationFormSidebarPriceDiscounts
                    event={event}
                    reservation={reservation}
                    onPayByCredit={props.onPayByCredit}
                    onSetDiscountCode={props.onSetDiscountCode}
                />
            </div>

        </div>
    )
};

export default ReservationFormSidebarPrice
