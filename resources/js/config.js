// общие настройки
export const config = {
    // Настройки всплывающего сообщения
    toast: {
        // Время отображения всплывающего сообщения (мс)
        timeLife: 5000,
    }
}

// корневая ссылка
const rootUrl = '/'

// настройка ссылок
export const urls = {
    
    // главная    
    home: rootUrl,

    /** 
     * аутентификация
     * @uses @\Pages\Auth\Login.vue
     */
    auth: {
        login() { return `${rootUrl}login` },
    },
    
    // пользователи
    users: {
        base: `${rootUrl}users`,

        index() { return `${this.base}` },
        create() { return `${this.base}/create` },
        store() { return this.index() },
        edit(id) { return `${this.update(id)}/edit` }, 
        update(id) { return `${this.base}/${id}` },
        delete(id) { return this.update(id) },
        restore(id) { return `${this.update(id)}/restore` },

        /**
         * привязанные организации к текущему пользователю
         * @uses @\Pages\Users\Organizations\Dialog.vue
         */
        organizations: {
            base: `${rootUrl}users/organizations`,

            index() { return `${this.base}` },
            change(id) { return `${this.base}/${id}` },
        },
    },    

    // графики
    chart: {
        base: `${rootUrl}chart`,

        /**
         * добавленные и установленные расходные материалы
         * за последние 30 дней
         * @uses @\Pages\Dashboard\Chart.vue 
         */
        last() { return `${this.base}/last` },

        /**
         * Добавленные расходные материалы за период
         * @uses @\Pages\Dashboard\ChartAdded.vue 
         */
        lastAdded() { return `${this.base}/last-added` },

        /**
         * Установленные расходные материалы за период
         * @uses @\Pages\Dashboard\ChartInstalled.vue 
         */
        lastInstalled() { return `${this.base}/last-installed` },
    },

    // принтеры на рабочих местах
    printers: {
        // @uses @\Pages\Printers\...
        base: `${rootUrl}printers/workplace`,
        
        // базовые операции
        index() { return this.base },
        create() { return `${this.base}/create` },
        store() { return this.index() },
        show(id) { return `${this.base}/${id}` },
        edit(id) { return `${this.base}/${id}/edit` },
        update(id) { return this.show(id) },
        delete(id) { return this.show(id) },

        /**
         * список принтеров, привязанных к расходному материалу idConsumable
         * используется в выпадающем списке
         * @uses @\Pages\Consumable\Count\Dialogs\Subtract.vue
         */     
        list(idConsumable) { return `${this.base}/list/${idConsumable}` },
                
        /**
         * все принтеры (используется в выпадающем списке)
         * @uses @\Pages\Consumable\InstalledDialog.vue
         */
        all() { return `${this.base}/all` },

        /**
         * расходные материалы, установленные на принтер
         * @uses @\Pages\Printers\Show\TabConsumablesInstall
         * @param {*} id 
         * @returns 
         */
        consumablesInstalled(id) {
            return `${this.base}/consumables-installed/${id}`;
        }
    },

    // расходные материалы
    consumables: {
        // доступное количество расходных материалов
        counts: {
            base: `${rootUrl}consumables/counts`,
                                                
            index() { return this.base },
            
            /**
             * @uses @\Pages\Consumable\Count\Index.vue
             */
            create()  { return `${this.base}/create` }, 
            
            /**
             * @uses @\Pages\Consumable\Count\Create.vue
             */
            store () { return this.base }, 
            
            /**
             * @uses @\Pages\Consumable\Count\Index.vue
             */
            show(id) { return `${this.base}/${id}` }, 
            
            /**
             * @uses @\Pages\Consumable\Count\Dialogs\Add.vue
             */
            update(id) { return this.show(id) }, 

            /**
             * @uses @\Pages\Consumable\Count\Dialogs\Correct.vue
             */
            correct(id) { return `${this.show(id)}/correct` }, 

            /**
             * @uses 
             */
            add(id) { return `${this.base}/${id}/add` },
            
            /**
             * сохранение вычитаемого расходного материала
             * @uses @\Pages\Consumable\Count\Dialogs\Add.vue
             * @uses @\Pages\Consumable\InstalledDialog.vue
             */
            subtract(consumable, count) { return `/consumables/${consumable}/counts/${count}/installed` },

            /**
             * валидация формы добавления количества расходного материала
             * @uses @\Pages\Consumable\Count\Create.vue
             */
            validate() { return `${this.base}/validate` },
            /**
             * поиск такого же расходного материала и с той же организацией
             * для дальнейшего прибавления количества, а не создания новой записи
             * @uses @\Pages\Consumable\Count\Create.vue
             */
            checkExists() { return `${this.base}/check-exists` },
            
            /**
             * @uses @\Pages\Consumable\Count\Show.vue
             */
            updateOrganizations(id) { return `${this.base}/${id}/update-organizations` },

            /**
             * Последние установленные(вычтенные) расходные материалы
             * @uses @\Pages\Dashboard\LastOperationsInstalled.vue
             */
            installed() { return `${this.base}/installed/last` },


            installMaster() { return `${this.base}/installed/master` },

            /**
             * список расходных материалов, привязанных к принтеру idPrinter
             * @uses @\Pages\Consumable\InstalledDialog.vue
             */
            listByPrinter(idPrinter) { return `${this.base}/list-by-printer/${idPrinter}` },

            /**
             * журналы движения количества расходных материалов
             * @uses resources\js\Pages\Consumable\Count\ShowJournal.vue
             */
            journal: {
                // журнал добавления расходных материалов                
                added: {
                    index: (idConsumable, idConsumableCount) => `/consumables/${idConsumable}/counts/${idConsumableCount}/added`,
                    redo: (idConsumable, idConsumableCount, id) => `/consumables/${idConsumable}/counts/${idConsumableCount}/added/${id}`,
                },
                // журнал установки(вычитания) расходных материалов
                installed: {
                    index: (idConsumable, idConsumableCount) => `/consumables/${idConsumable}/counts/${idConsumableCount}/installed`,
                    redo: (idConsumable, idConsumableCount, id) => `/consumables/${idConsumable}/counts/${idConsumableCount}/installed/${id}`,
                },
            },                        
        },
    },

    // справочник
    dictionary: {

        // справочник принтеров
        printers: {
            base: `${rootUrl}dictionary/printers`,

            // базовые операции
            index() { return `${this.base}` },
            create: () => `/dictionary/printers/create`,
            show: (id) => `/dictionary/printers/${id}`,
            edit: (id) =>  `/dictionary/printers/${id}/edit`,
            update: (id) => `/dictionary/printers/${id}`,
            delete: (id) => `/dictionary/printers/${id}`,
            
            // привязанные расходные материалы к принтеру
            consumables: {
                index: (id) => `/dictionary/printers/${id}/consumables`,
                add: (idPrinter, idConsumable) => `/dictionary/printers/${idPrinter}/consumables/${idConsumable}/add`,
                delete: (idPrinter, idConsumable) => `/dictionary/printers/${idPrinter}/consumables/${idConsumable}`,
            },
        },

        // справочник расходных материалов
        consumables: {
            base: `${rootUrl}dictionary/consumables`,

            index() { return this.base },
            
            /**
             * @uses @\Pages\Dictionary\Consumables\Form.vue
             */
            store() { return this.index() },
            create() { return `${this.base}/create` },
            
            /**
             * @uses @\Pages\Dictionary\Consumables\Index.vue
             */
            show(id) { return `${this.base}/${id}` },

            /**
             * @uses @\Pages\Dictionary\Consumables\Show.vue
             */
            edit(id) { return `${this.base}/${id}/edit` },           
            
            /**
             * @uses @\Pages\Dictionary\Consumables\Form.vue
             */
            update(id) { return this.show(id) },

            /**
             * @uses @\Pages\Dictionary\Consumables\Show.vue
             */
            delete(id) { return this.show(id) },
            
            // привязанные принтеры к расходному материалу
            printers: {
                base(idConsumable) { return `/dictionary/consumables/${idConsumable}/printers` },
                
                index(idConsumable) { return this.base(idConsumable) },

                /**
                 * @uses @\Pages\Dictionary\Consumables\Printers\Index.vue 
                 */
                add(idConsumable, idPrinter) { return `${this.base(idConsumable)}/${idPrinter}/add` },

                /**
                 * @uses @\Pages\Dictionary\Consumables\Show.vue 
                 */
                delete(idConsumable, idPrinter) { return `${this.base(idConsumable)}/${idPrinter}` },
            },
        },

        // справочник организаций
        organizations: {  
            // @uses @\Pages\Dictionary\Organizations\...

            base: `${rootUrl}dictionary/organizations`,
            
            index() { return this.base },
            store() { return this.index() },
            create() { return `${this.base}/create` },
            show(id) { return `${this.base}/${id}` },
            edit(id) { return `${this.base}/${id}/edit` },           
            update(id) { return this.show(id) },
            delete(id) { return this.show(id) },
        },        

        // запчасти
        spareParts: {
            base: `${rootUrl}dictionary/spare-parts`,

            index() { return this.base },
            
            create() { return `${this.base}/create` },
            store() { return `${this.base}` },

            edit(id) { return `${this.base}/${id}/edit` },
            update(id) { return `${this.base}/${id}` },

            delete(id) { return `${this.base}/${id}` },            
        },

    },

    // отчеты
    reports: {
        // @uses @\Pages\Reports\...

        base: `${rootUrl}reports`,

        index() { return this.base },
        exportPrintersWorkplace() { return `${this.base}/export-printers-workplace` },
        exportConsumableCount() { return `${this.base}/export-consumable-count` },
        exportConsumableInstalledCount() { return `${this.base}/export-consumable-installed-count` },
    },

    orders: {

        base: `${rootUrl}orders`,

        approve(id_order) {
            return `${this.base}/${id_order}/approve`;
        },
        reject(id_order) {
            return `${this.base}/${id_order}/reject`;
        },
        completed(id_order) {
            return `${this.base}/${id_order}/completed`;
        },

        
        spareParts: {
            base: `${rootUrl}orders/spare-parts`,

            index() { return this.base },
            
            create() { return `${this.base}/create` },
            store() { return `${this.base}` },
            
            edit(id) { return `${this.base}/${id}/edit` },
            update(id) { return `${this.base}/${id}` },

            show(id) { return `${this.base}/${id}` },
        }

    },

    // статистика
    logActions: {
        // сохранение
        save: 'http://86000-app045:4785/view/save',
    },

}