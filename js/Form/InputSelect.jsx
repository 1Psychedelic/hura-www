import React from 'react'


const InputSelect = React.forwardRef((props, ref) => {

    let createOption = (value, label, ) => {
        return (
            <option key={value} value={value}>{label}</option>
        );
    };

    let createOptions = (options) => {
        return options.map((option) => {
            return createOption(option.value, option.label);
        })
    };

    return (
        <div className={'form-input-group form-input-group-select' + (props.data.value ? ' active' : '') + (props.data.validationError ? ' form-error' : '')}>
            <div className="form-input-group-inner">
                <label htmlFor={props.name}>{props.label}</label>
                <select ref={ref} onChange={props.onChange} id={props.name} name={props.name} value={props.data.value || ''}>
                    {createOptions(props.data.options)}
                </select>
                <span className="chevron"/>
            </div>
            <div className="form-error-message">{props.data.validationError}</div>
        </div>
    )
});

export default InputSelect
