import { inject } from 'lib/Injector';
import React, { PropTypes } from 'react';

const Meatballs = ({ PopoverField, id, children, container }) => (
  <PopoverField
    id={id}
    buttonSize="sm"
    data={{ placement: 'bottom' }}
    className="mr-0 btn-sm"
    popoverClassName="meatball-menu__actions"
    container={container}
  >
    {children}
  </PopoverField>
);

Meatballs.propTypes = {
  id: PropTypes.string.isRequired,
  PopoverField: PropTypes.oneOfType([PropTypes.node, PropTypes.func]),
};

Meatballs.defaultProps = {
  id: '',
  PopoverField: null,
};

export { Meatballs as Component };

export default inject(
  ['PopoverField'],
  (PopoverField) => ({ PopoverField }),
  () => 'Meatballs'
)(Meatballs);
