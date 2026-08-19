import React, { useEffect, useRef, useState } from 'react';
import Sidebar from './Sidebar';
import Topbar from './Topbar';

function ChatbotPanel() {
    return (
        <div className="chatbot-panel active" id="chatbotPanel">
            <div className="chatbot-messages">
                <div className="chatbot-message bot">
                    <strong>Pil Bot:</strong>
                    <br />
                    Hola! Soy tu asistente virtual. Muy pronto podre ayudarte con consultas sobre pedidos, inventario y KPIs.
                </div>
                <div className="chatbot-message user">
                    Genial, estare atento a las nuevas funciones.
                </div>
            </div>
            <div className="chatbot-input">
                <input type="text" placeholder="Pronto podras escribirme aqui..." disabled />
                <button className="pill-button" disabled>Enviar</button>
            </div>
        </div>
    );
}

function DashboardShell({
    sidebar,
    topbar,
    csrfToken,
    logoutAction,
    children,
}) {
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [chatbotOpen, setChatbotOpen] = useState(false);
    const chatbotPanelRef = useRef(null);
    const chatbotButtonRef = useRef(null);

    useEffect(() => {
        if (!chatbotOpen) {
            return undefined;
        }

        const handleDocumentClick = (event) => {
            if (
                chatbotPanelRef.current &&
                !chatbotPanelRef.current.contains(event.target) &&
                chatbotButtonRef.current &&
                !chatbotButtonRef.current.contains(event.target)
            ) {
                setChatbotOpen(false);
            }
        };

        document.addEventListener('click', handleDocumentClick);

        return () => {
            document.removeEventListener('click', handleDocumentClick);
        };
    }, [chatbotOpen]);

    return (
        <div className="dashboard-shell">
            <Sidebar
                logoUrl={sidebar.logoUrl}
                items={sidebar.items}
                isOpen={sidebarOpen}
                onClose={() => setSidebarOpen(false)}
            />
            <main className="main-area">
                <Topbar
                    pageTitle={topbar.pageTitle}
                    user={topbar.user}
                    csrfToken={csrfToken}
                    logoutAction={logoutAction}
                    onSidebarToggle={() => setSidebarOpen((value) => !value)}
                />
                <section className="content-scroll">
                    {children}
                </section>
            </main>

            {chatbotOpen ? <div ref={chatbotPanelRef}><ChatbotPanel /></div> : null}
            <button
                ref={chatbotButtonRef}
                className="chatbot-button"
                id="chatbotToggle"
                title="Chatbot experimental"
                type="button"
                onClick={() => setChatbotOpen((value) => !value)}
            >
                <i className="ri-message-3-line" />
            </button>
        </div>
    );
}

export default React.memo(DashboardShell);
