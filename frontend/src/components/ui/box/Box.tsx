import React from 'react'
import styles from './Box.module.css'

function Box({ children }: { children: React.ReactNode }) {
  return <div className={styles.box}>{children}</div>;
}

export default Box;
