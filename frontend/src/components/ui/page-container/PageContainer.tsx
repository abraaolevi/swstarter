import Header from "../header/Header"
import styles from "./PageContainer.module.css"

function PageContainer({ children }: { children: React.ReactNode }) {
  return (
    <div className={styles.pageContainer}>
      <Header />
      {children}
    </div>
  );
}

export default PageContainer;
