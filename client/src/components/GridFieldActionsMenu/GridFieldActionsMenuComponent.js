import { inject } from 'lib/Injector';
import React, { PropTypes } from 'react';

const ActionsMenu = ({ PopoverField, id, children, container }) => (
  <PopoverField
    id={id}
    buttonSize="sm"
    data={{ placement: 'bottom' }}
    className="mr-0 btn-sm"
    popoverClassName="actions-menu__actions"
    container={container}
  >
    {children}
  </PopoverField>
);

ActionsMenu.propTypes = {
  id: PropTypes.string.isRequired,
  PopoverField: PropTypes.oneOfType([PropTypes.node, PropTypes.func]),
};

ActionsMenu.defaultProps = {
  id: '',
  PopoverField: null,
};

export { ActionsMenu as Component };

export default inject(
  ['PopoverField'],
  (PopoverField) => ({ PopoverField }),
  () => 'ActionsMenu'
)(ActionsMenu);
